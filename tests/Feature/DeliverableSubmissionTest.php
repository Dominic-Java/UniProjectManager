<?php

namespace Tests\Feature;

use App\Mail\DeliverableSubmissionGradedMail;
use App\Models\Classroom;
use App\Models\Deliverable;
use App\Models\DeliverableSubmission;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeliverableSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_upload_deliverable_submission_file(): void
    {
        Storage::fake('local');

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, $student, 'Baze de date');

        $project = Project::create([
            'title' => 'Proiect Fisiere',
            'description' => 'Descriere',
            'domain' => 'Baze de date',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(20)->toDateString(),
            'deadline_at' => Carbon::now()->addDay(),
            'created_by' => $professor->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Predare finala',
            'description' => 'Incarca arhiva proiectului',
            'due_at' => Carbon::now()->addHours(12),
            'submission_type' => 'file',
            'max_points' => 100,
            'created_by' => $professor->id,
        ]);

        $this->actingAs($student)
            ->post(route('deliverables.submit', $deliverable), [
                'submission_file' => UploadedFile::fake()->create('tema.rar', 1200, 'application/x-rar-compressed'),
                'notes' => 'Varianta finala.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $submission = \App\Models\DeliverableSubmission::query()
            ->where('deliverable_id', $deliverable->id)
            ->where('student_user_id', $student->id)
            ->first();

        $this->assertNotNull($submission);
        Storage::disk('local')->assertExists($submission->file_path);

        $storedRawNotes = DB::table('deliverable_submissions')
            ->where('id', $submission->id)
            ->value('notes');
        $this->assertIsString($storedRawNotes);
        $this->assertNotSame('Varianta finala.', $storedRawNotes);
        $this->assertSame('Varianta finala.', $submission->notes);
    }

    public function test_student_cannot_upload_when_project_is_locked(): void
    {
        Storage::fake('local');

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, $student, 'IA');

        $project = Project::create([
            'title' => 'Proiect Inchis',
            'description' => 'Descriere',
            'domain' => 'IA',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(10)->toDateString(),
            'deadline_at' => Carbon::now()->subMinute(),
            'created_by' => $professor->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Predare',
            'description' => null,
            'due_at' => Carbon::now()->addHour(),
            'submission_type' => 'file',
            'max_points' => 100,
            'created_by' => $professor->id,
        ]);

        $this->actingAs($student)
            ->post(route('deliverables.submit', $deliverable), [
                'submission_file' => UploadedFile::fake()->create('tema.zip', 200),
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('deliverable_submissions', 0);
    }

    public function test_student_can_cancel_submission_and_reupload_later(): void
    {
        Storage::fake('local');

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, $student, 'PAOO');

        $project = Project::create([
            'title' => 'Proiect Reupload',
            'description' => 'Descriere',
            'domain' => 'PAOO',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDay()->toDateString(),
            'end_date' => Carbon::now()->addDays(10)->toDateString(),
            'deadline_at' => Carbon::now()->addDays(2),
            'created_by' => $professor->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Predare intermediar',
            'description' => null,
            'due_at' => Carbon::now()->addDay(),
            'submission_type' => 'file',
            'max_points' => 100,
            'created_by' => $professor->id,
        ]);

        $this->actingAs($student)->post(route('deliverables.submit', $deliverable), [
            'submission_file' => UploadedFile::fake()->create('draft.zip', 100),
        ]);

        $submission = \App\Models\DeliverableSubmission::query()
            ->where('deliverable_id', $deliverable->id)
            ->where('student_user_id', $student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->actingAs($student)
            ->delete(route('deliverables.submissions.cancel', $submission))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('deliverable_submissions', 0);

        $this->actingAs($student)->post(route('deliverables.submit', $deliverable), [
            'submission_file' => UploadedFile::fake()->create('final.zip', 120),
        ]);

        $this->assertDatabaseCount('deliverable_submissions', 1);
    }

    public function test_professor_can_grade_submission_and_student_receives_email(): void
    {
        Storage::fake('local');
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, $student, 'Retele');

        $project = Project::create([
            'title' => 'Proiect notare',
            'description' => 'Descriere',
            'domain' => 'Retele',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDay()->toDateString(),
            'end_date' => Carbon::now()->addDays(7)->toDateString(),
            'deadline_at' => Carbon::now()->addDays(2),
            'created_by' => $professor->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Predare notata',
            'description' => null,
            'due_at' => Carbon::now()->addDay(),
            'submission_type' => 'file',
            'max_points' => 100,
            'created_by' => $professor->id,
        ]);

        $this->actingAs($student)->post(route('deliverables.submit', $deliverable), [
            'submission_file' => UploadedFile::fake()->create('lucrare.zip', 100),
        ]);

        $submission = DeliverableSubmission::query()
            ->where('deliverable_id', $deliverable->id)
            ->where('student_user_id', $student->id)
            ->first();

        $this->assertNotNull($submission);

        $this->actingAs($professor)
            ->post(route('deliverables.submissions.grade', $submission), [
                'grade_points' => 95.5,
                'grade_feedback' => 'Foarte bine structurat, mai lucreaza la documentatie.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $submission->refresh();
        $this->assertSame('95.50', $submission->grade_points);
        $this->assertSame($professor->id, $submission->graded_by_user_id);
        $this->assertNotNull($submission->graded_at);
        $this->assertSame('Foarte bine structurat, mai lucreaza la documentatie.', $submission->grade_feedback);

        Mail::assertSent(DeliverableSubmissionGradedMail::class, function (DeliverableSubmissionGradedMail $mail) use ($submission, $student): bool {
            return $mail->hasTo($student->email)
                && $mail->submission->id === $submission->id
                && $mail->gradedBy->id === $submission->graded_by_user_id;
        });
    }

    public function test_admin_can_grade_submission_created_in_another_professor_project(): void
    {
        Storage::fake('local');
        Mail::fake();

        config()->set('uniprojectmanager.admin_emails', ['admin@ulbs.ro']);

        $admin = User::factory()->create([
            'email' => 'admin@ulbs.ro',
            'role' => 'student',
        ]);
        $ownerProfessor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($ownerProfessor, $student, 'PAOO');

        $project = Project::create([
            'title' => 'Proiect acces admin',
            'description' => 'Descriere',
            'domain' => 'PAOO',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDay()->toDateString(),
            'end_date' => Carbon::now()->addDays(7)->toDateString(),
            'deadline_at' => Carbon::now()->addDays(2),
            'created_by' => $ownerProfessor->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Predare admin',
            'description' => null,
            'due_at' => Carbon::now()->addDay(),
            'submission_type' => 'file',
            'max_points' => 50,
            'created_by' => $ownerProfessor->id,
        ]);

        $this->actingAs($student)->post(route('deliverables.submit', $deliverable), [
            'submission_file' => UploadedFile::fake()->create('tema-finala.zip', 120),
        ]);

        $submission = DeliverableSubmission::query()
            ->where('deliverable_id', $deliverable->id)
            ->where('student_user_id', $student->id)
            ->first();

        $this->assertNotNull($submission);

        $this->actingAs($admin)
            ->post(route('deliverables.submissions.grade', $submission), [
                'grade_points' => 47,
                'grade_feedback' => 'Corectat la nivel administrativ.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $submission->refresh();
        $this->assertSame('47.00', $submission->grade_points);
        $this->assertSame($admin->id, $submission->graded_by_user_id);

        Mail::assertSent(DeliverableSubmissionGradedMail::class, 1);
    }

    public function test_grading_rejects_values_over_max_points(): void
    {
        Storage::fake('local');
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = $this->createClassroomWithMembers($professor, $student, 'IA');

        $project = Project::create([
            'title' => 'Proiect validare nota',
            'description' => 'Descriere',
            'domain' => 'IA',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDay()->toDateString(),
            'end_date' => Carbon::now()->addDays(7)->toDateString(),
            'deadline_at' => Carbon::now()->addDays(2),
            'created_by' => $professor->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Predare cu punctaj mic',
            'description' => null,
            'due_at' => Carbon::now()->addDay(),
            'submission_type' => 'file',
            'max_points' => 10,
            'created_by' => $professor->id,
        ]);

        $this->actingAs($student)->post(route('deliverables.submit', $deliverable), [
            'submission_file' => UploadedFile::fake()->create('fisier.zip', 80),
        ]);

        $submission = DeliverableSubmission::query()
            ->where('deliverable_id', $deliverable->id)
            ->where('student_user_id', $student->id)
            ->first();

        $this->assertNotNull($submission);

        $this->actingAs($professor)
            ->post(route('deliverables.submissions.grade', $submission), [
                'grade_points' => 11,
            ])
            ->assertSessionHasErrors('grade_points');

        $submission->refresh();
        $this->assertNull($submission->grade_points);
        $this->assertNull($submission->graded_by_user_id);
        Mail::assertNothingSent();
    }

    private function createClassroomWithMembers(User $professor, User $student, string $subject): Classroom
    {
        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa ' . $subject,
            'subject' => $subject,
            'created_by' => $professor->id,
            'is_active' => true,
        ]);

        DB::table('classroom_members')->insert([
            [
                'classroom_id' => $classroom->id,
                'user_id' => $professor->id,
                'role' => 'teacher',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $classroom->id,
                'user_id' => $student->id,
                'role' => 'student',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return $classroom;
    }
}
