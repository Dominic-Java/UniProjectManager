<?php

namespace Tests\Feature;

use App\Mail\ClassroomJoinedConfirmationMail;
use App\Models\Classroom;
use App\Models\ClassroomInvitation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClassroomFlowAndAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_can_create_classroom_invite_student_and_student_accepts(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($professor)
            ->post(route('classrooms.store'), [
                'name' => 'Seria A',
                'subject' => 'Ingineria Programarii',
                'description' => 'Detalii clasa',
            ])
            ->assertRedirect();

        $classroom = Classroom::query()->where('created_by', $professor->id)->first();
        $this->assertNotNull($classroom);
        $this->assertStringStartsWith('CLS-', $classroom->code);

        $this->assertDatabaseHas('classroom_members', [
            'classroom_id' => $classroom->id,
            'user_id' => $professor->id,
            'role' => 'teacher',
        ]);

        $this->actingAs($professor)
            ->post(route('classrooms.invitations.send', $classroom), [
                'email' => $student->email,
                'message' => 'Te invit in classroom',
            ])
            ->assertRedirect();

        $invitation = ClassroomInvitation::query()
            ->where('classroom_id', $classroom->id)
            ->where('student_user_id', $student->id)
            ->first();

        $this->assertNotNull($invitation);
        $this->assertSame('pending', $invitation->status);

        $this->actingAs($student)
            ->post(route('classrooms.invitations.respond', $invitation), [
                'action' => 'accept',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('classroom_members', [
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
            'role' => 'student',
        ]);

        Mail::assertSent(ClassroomJoinedConfirmationMail::class, function (ClassroomJoinedConfirmationMail $mail) use ($student, $classroom): bool {
            return $mail->hasTo($student->email)
                && $mail->classroom->id === $classroom->id
                && $mail->joinedVia === 'invitation';
        });
    }

    public function test_student_can_join_classroom_by_code(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa POO',
            'subject' => 'POO',
            'created_by' => $professor->id,
            'is_active' => true,
        ]);

        DB::table('classroom_members')->insert([
            'classroom_id' => $classroom->id,
            'user_id' => $professor->id,
            'role' => 'teacher',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($student)
            ->post(route('classrooms.join'), [
                'classroom_code' => $classroom->code,
            ])
            ->assertRedirect(route('classrooms.show', $classroom));

        $this->assertDatabaseHas('classroom_members', [
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
            'role' => 'student',
        ]);

        Mail::assertSent(ClassroomJoinedConfirmationMail::class, function (ClassroomJoinedConfirmationMail $mail) use ($student, $classroom): bool {
            return $mail->hasTo($student->email)
                && $mail->classroom->id === $classroom->id
                && $mail->joinedVia === 'code';
        });
    }

    public function test_professor_cannot_manage_project_from_another_professor_classroom(): void
    {
        $owner = User::factory()->create(['role' => 'profesor']);
        $otherProfessor = User::factory()->create(['role' => 'profesor']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa Retele',
            'subject' => 'Retele',
            'created_by' => $owner->id,
            'is_active' => true,
        ]);

        DB::table('classroom_members')->insert([
            [
                'classroom_id' => $classroom->id,
                'user_id' => $owner->id,
                'role' => 'teacher',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $classroom->id,
                'user_id' => $otherProfessor->id,
                'role' => 'teacher',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $project = Project::create([
            'title' => 'Proiect securitate',
            'description' => 'Descriere',
            'domain' => 'Retele',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($otherProfessor)
            ->get(route('projects.edit', $project))
            ->assertForbidden();
    }

    public function test_user_can_toggle_theme_preference(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'theme_preference' => 'light',
        ]);

        $this->actingAs($user)
            ->post(route('profile.theme.toggle'))
            ->assertRedirect();

        $this->assertSame('dark', $user->fresh()->theme_preference);
    }

    public function test_login_syncs_theme_from_cookie_to_user_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'theme_preference' => 'light',
        ]);

        $this->withUnencryptedCookie('upm_theme', 'dark')
            ->post(route('login.submit'), [
                'email' => $user->email,
                'password' => 'password',
                'role' => 'student',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertSame('dark', $user->fresh()->theme_preference);
    }
}
