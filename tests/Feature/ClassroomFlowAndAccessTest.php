<?php

namespace Tests\Feature;

use App\Mail\ClassroomInvitationMail;
use App\Mail\ClassroomJoinedConfirmationMail;
use App\Models\Classroom;
use App\Models\ClassroomInvitation;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

        Mail::assertSent(ClassroomInvitationMail::class, function (ClassroomInvitationMail $mail) use ($student, $classroom, $professor): bool {
            return $mail->hasTo($student->email)
                && $mail->classroom->id === $classroom->id
                && $mail->invitedBy->id === $professor->id;
        });

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

    public function test_professor_can_send_bulk_invitations_to_multiple_students(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $studentOne = User::factory()->create(['role' => 'student']);
        $studentTwo = User::factory()->create(['role' => 'student']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa Web',
            'subject' => 'Programare Web',
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

        $this->actingAs($professor)
            ->post(route('classrooms.invitations.send', $classroom), [
                'emails' => $studentOne->email . "\n" . $studentTwo->email,
                'message' => 'Va astept la urmatorul laborator.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('classroom_invitations', [
            'classroom_id' => $classroom->id,
            'student_user_id' => $studentOne->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('classroom_invitations', [
            'classroom_id' => $classroom->id,
            'student_user_id' => $studentTwo->id,
            'status' => 'pending',
        ]);

        Mail::assertSent(ClassroomInvitationMail::class, 2);
    }

    public function test_professor_can_archive_and_delete_owned_classroom(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa de arhivat',
            'subject' => 'Sisteme distribuite',
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

        $this->actingAs($professor)
            ->put(route('classrooms.archive', $classroom))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'is_active' => 0,
        ]);

        $this->actingAs($professor)
            ->delete(route('classrooms.destroy', $classroom))
            ->assertRedirect(route('classrooms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('classrooms', [
            'id' => $classroom->id,
        ]);
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

    public function test_admin_can_access_projects_and_teams_from_all_professors(): void
    {
        config()->set('uniprojectmanager.admin_emails', ['admin@ulbs.ro']);

        $admin = User::factory()->create([
            'email' => 'admin@ulbs.ro',
            'role' => 'profesor',
        ]);
        $ownerProfessor = User::factory()->create([
            'email' => 'owner.prof@ulbs.ro',
            'role' => 'profesor',
        ]);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa Admin Access',
            'subject' => 'Sisteme de operare',
            'created_by' => $ownerProfessor->id,
            'is_active' => true,
        ]);

        $project = Project::create([
            'title' => 'Proiect cross-profesor',
            'description' => 'Test pentru acces admin global.',
            'domain' => 'Sisteme de operare',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'created_by' => $ownerProfessor->id,
        ]);

        $team = Team::create([
            'project_id' => $project->id,
            'name' => 'Echipa globala',
            'status' => 'active',
            'created_by' => $ownerProfessor->id,
        ]);

        $this->actingAs($admin)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Proiect cross-profesor');

        $this->actingAs($admin)
            ->get(route('projects.show', $project))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('projects.edit', $project))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('teams.index'))
            ->assertOk()
            ->assertSee('Echipa globala');

        $this->actingAs($admin)
            ->get(route('teams.show', $team))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('teams.edit', $team))
            ->assertOk();
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

    public function test_user_can_upload_profile_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'student',
            'first_name' => 'Alex',
            'last_name' => 'Pop',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'first_name' => 'Alex',
                'last_name' => 'Pop',
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 120, 120),
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->avatar_url);

        $storedPath = ltrim(str_replace('/storage/', '', (string) $user->avatar_url), '/');
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_student_professor_and_admin_can_change_language_from_profile(): void
    {
        config()->set('uniprojectmanager.admin_emails', ['admin@ulbs.ro']);

        $users = [
            User::factory()->create([
                'role' => 'student',
                'first_name' => 'Student',
                'last_name' => 'User',
                'locale_preference' => 'ro',
            ]),
            User::factory()->create([
                'role' => 'profesor',
                'first_name' => 'Profesor',
                'last_name' => 'User',
                'locale_preference' => 'ro',
            ]),
            User::factory()->create([
                'role' => 'profesor',
                'email' => 'admin@ulbs.ro',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'locale_preference' => 'ro',
            ]),
        ];

        foreach ($users as $user) {
            $this->actingAs($user)
                ->put(route('profile.update'), [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'locale_preference' => 'en',
                ])
                ->assertRedirect();

            $this->assertSame('en', $user->fresh()->locale_preference);
        }

        $this->actingAs($users[0]->fresh())
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Language')
            ->assertSee('Save changes');
    }

    public function test_professor_sees_only_owned_classrooms_and_cannot_open_other_professor_classroom(): void
    {
        $ownerProfessor = User::factory()->create(['role' => 'profesor']);
        $otherProfessor = User::factory()->create(['role' => 'profesor']);

        $ownerClassroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa B',
            'subject' => 'Baze de date',
            'created_by' => $ownerProfessor->id,
            'is_active' => true,
        ]);

        $otherClassroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa C',
            'subject' => 'Cloud',
            'created_by' => $otherProfessor->id,
            'is_active' => true,
        ]);

        DB::table('classroom_members')->insert([
            [
                'classroom_id' => $ownerClassroom->id,
                'user_id' => $ownerProfessor->id,
                'role' => 'teacher',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $ownerClassroom->id,
                'user_id' => $otherProfessor->id,
                'role' => 'teacher',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $otherClassroom->id,
                'user_id' => $otherProfessor->id,
                'role' => 'teacher',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($otherProfessor)
            ->get(route('classrooms.index'))
            ->assertOk()
            ->assertSee('Clasa C')
            ->assertDontSee('Clasa B');

        $this->actingAs($otherProfessor)
            ->get(route('classrooms.show', $ownerClassroom))
            ->assertForbidden();
    }
}
