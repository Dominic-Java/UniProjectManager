<?php

namespace Tests\Feature;

use App\Mail\ClassroomGradeAssignedMail;
use App\Mail\ClassroomFailingGradeWarningMail;
use App\Mail\ClassroomRetakeDetailsMail;
use App\Models\Classroom;
use App\Models\ClassroomGrade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CatalogAndReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_can_grade_student_and_student_can_view_catalog(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, [$student], 'Programare Avansata');

        $this->actingAs($professor)
            ->post(route('catalog.grades.store', $classroom), [
                'student_user_id' => $student->id,
                'grade_value' => 8.5,
                'feedback' => 'Progres bun.',
            ])
            ->assertRedirect(route('catalog.index', ['classroom_id' => $classroom->id]));

        $this->assertDatabaseHas('classroom_grades', [
            'classroom_id' => $classroom->id,
            'student_user_id' => $student->id,
            'grade_value' => '8.50',
        ]);

        $this->actingAs($student)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Programare Avansata')
            ->assertSee('8.50')
            ->assertSee('Promovat');
    }

    public function test_failing_grade_triggers_warning_email(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, [$student], 'Algoritmi');

        $this->actingAs($professor)
            ->post(route('catalog.grades.store', $classroom), [
                'student_user_id' => $student->id,
                'grade_value' => 4.25,
                'feedback' => 'Mai ai de recuperat.',
            ])
            ->assertRedirect();

        Mail::assertSent(ClassroomFailingGradeWarningMail::class, function (ClassroomFailingGradeWarningMail $mail) use ($student): bool {
            return $mail->hasTo($student->email);
        });

        $grade = ClassroomGrade::query()
            ->where('classroom_id', $classroom->id)
            ->where('student_user_id', $student->id)
            ->first();

        $this->assertNotNull($grade?->last_warning_sent_at);
    }

    public function test_any_assigned_grade_sends_grade_email_and_notification(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, [$student], 'Matematica');

        $this->actingAs($professor)
            ->post(route('catalog.grades.store', $classroom), [
                'student_user_id' => $student->id,
                'grade_value' => 9.75,
                'feedback' => 'Foarte bine.',
            ])
            ->assertRedirect();

        Mail::assertSent(ClassroomGradeAssignedMail::class, function (ClassroomGradeAssignedMail $mail) use ($student): bool {
            return $mail->hasTo($student->email) && !$mail->isUpdate;
        });
        Mail::assertNotSent(ClassroomFailingGradeWarningMail::class);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $student->id,
            'type' => 'catalog.grade.assigned',
        ]);
    }

    public function test_professor_can_send_manual_retake_details_only_to_failing_students(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $failingStudent = User::factory()->create(['role' => 'student']);
        $passingStudent = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, [$failingStudent, $passingStudent], 'Retele');

        ClassroomGrade::create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $failingStudent->id,
            'graded_by_user_id' => $professor->id,
            'grade_value' => 3.5,
        ]);
        ClassroomGrade::create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $passingStudent->id,
            'graded_by_user_id' => $professor->id,
            'grade_value' => 7.0,
        ]);

        $this->actingAs($professor)
            ->post(route('catalog.retake-emails.send', $classroom), [
                'student_ids' => [$failingStudent->id, $passingStudent->id],
                'details' => 'Refaceti proiectul cu cerinte extinse pana pe 15 aprilie.',
            ])
            ->assertRedirect(route('catalog.index', ['classroom_id' => $classroom->id]))
            ->assertSessionHas('success');

        Mail::assertSent(ClassroomRetakeDetailsMail::class, function (ClassroomRetakeDetailsMail $mail) use ($failingStudent): bool {
            return $mail->hasTo($failingStudent->email);
        });
        Mail::assertSent(ClassroomRetakeDetailsMail::class, 1);
    }

    public function test_catalog_reminder_command_sends_emails_for_failing_grades(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, [$student], 'PAOO');

        ClassroomGrade::create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $student->id,
            'graded_by_user_id' => $professor->id,
            'grade_value' => 2.0,
        ]);

        $this->artisan('catalog:send-failing-grade-reminders')
            ->assertSuccessful();

        Mail::assertSent(ClassroomFailingGradeWarningMail::class, 1);
    }

    public function test_catalog_feedback_is_encrypted_at_rest(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, [$student], 'Criptografie');
        $feedback = 'Mesaj privat pentru student.';

        $this->actingAs($professor)
            ->post(route('catalog.grades.store', $classroom), [
                'student_user_id' => $student->id,
                'grade_value' => 6.40,
                'feedback' => $feedback,
            ])
            ->assertRedirect();

        $storedRawFeedback = DB::table('classroom_grades')
            ->where('classroom_id', $classroom->id)
            ->where('student_user_id', $student->id)
            ->value('feedback');

        $grade = ClassroomGrade::query()
            ->where('classroom_id', $classroom->id)
            ->where('student_user_id', $student->id)
            ->first();

        $this->assertIsString($storedRawFeedback);
        $this->assertNotSame($feedback, $storedRawFeedback);
        $this->assertSame($feedback, $grade?->feedback);
    }

    private function createClassroomWithMembers(User $professor, array $students, string $subject): Classroom
    {
        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa ' . $subject,
            'subject' => $subject,
            'created_by' => $professor->id,
            'is_active' => true,
        ]);

        $rows = [[
            'classroom_id' => $classroom->id,
            'user_id' => $professor->id,
            'role' => 'teacher',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]];

        foreach ($students as $student) {
            $rows[] = [
                'classroom_id' => $classroom->id,
                'user_id' => $student->id,
                'role' => 'student',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('classroom_members')->insert($rows);

        return $classroom;
    }
}
