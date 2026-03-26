<?php

namespace Tests\Feature;

use App\Mail\NewProjectCreatedMail;
use App\Mail\ProjectMaterialUploadedMail;
use App\Mail\ProjectRequirementAddedMail;
use App\Models\Classroom;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectNotificationAndMaterialTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_creating_project_sends_email_with_subject_name(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa A',
            'subject' => 'Algoritmi',
            'created_by' => $professor->id,
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\DB::table('classroom_members')->insert([
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

        $this->actingAs($professor)
            ->post(route('projects.store'), [
                'classroom_id' => $classroom->id,
                'title' => 'Proiect Final',
                'description' => 'Descriere proiect',
                'visibility' => 'public',
                'status' => 'open',
                'min_team_size' => 1,
                'max_team_size' => 4,
            ])
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('success');

        Mail::assertSent(NewProjectCreatedMail::class, function (NewProjectCreatedMail $mail) use ($student): bool {
            return $mail->hasTo($student->email) && $mail->project->domain === 'Algoritmi';
        });
    }

    public function test_professor_can_upload_classwork_material_and_student_can_download_it(): void
    {
        Storage::fake('local');

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);
        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa OOP',
            'subject' => 'Programare Orientata pe Obiect',
            'created_by' => $professor->id,
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\DB::table('classroom_members')->insert([
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

        $project = Project::create([
            'title' => 'Classwork OOP',
            'description' => 'Descriere',
            'domain' => 'Programare Orientata pe Obiect',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 5,
            'created_by' => $professor->id,
        ]);

        $this->actingAs($professor)
            ->post(route('projects.materials.store', $project), [
                'title' => 'Curs 1',
                'material_file' => UploadedFile::fake()->image('curs1.png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $material = ProjectMaterial::query()->where('project_id', $project->id)->first();
        $this->assertNotNull($material);
        Storage::disk('local')->assertExists($material->file_path);

        $this->actingAs($student)
            ->get(route('projects.materials.download', $material))
            ->assertOk();
    }

    public function test_teacher_member_professor_can_upload_classwork_material(): void
    {
        Storage::fake('local');

        $ownerProfessor = User::factory()->create(['role' => 'profesor']);
        $teacherProfessor = User::factory()->create(['role' => 'profesor']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa IP',
            'subject' => 'Ingineria Programarii',
            'created_by' => $ownerProfessor->id,
            'is_active' => true,
        ]);

        DB::table('classroom_members')->insert([
            [
                'classroom_id' => $classroom->id,
                'user_id' => $ownerProfessor->id,
                'role' => 'teacher',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $classroom->id,
                'user_id' => $teacherProfessor->id,
                'role' => 'teacher',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $project = Project::create([
            'title' => 'Classwork OOP',
            'description' => 'Descriere',
            'domain' => 'Ingineria Programarii',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 5,
            'created_by' => $ownerProfessor->id,
        ]);

        $this->actingAs($teacherProfessor)
            ->post(route('projects.materials.store', $project), [
                'title' => 'Laborator 2',
                'material_file' => UploadedFile::fake()->create('lab2.pdf', 120),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('project_materials', [
            'project_id' => $project->id,
            'title' => 'Laborator 2',
            'uploaded_by' => $teacherProfessor->id,
        ]);
    }

    public function test_uploading_project_material_sends_email_to_classroom_members_except_uploader(): void
    {
        Mail::fake();
        Storage::fake('local');

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa WEB',
            'subject' => 'Programare Web',
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

        $project = Project::create([
            'title' => 'Platforma licenta',
            'description' => 'Descriere proiect',
            'domain' => 'Programare Web',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 5,
            'created_by' => $professor->id,
        ]);

        $this->actingAs($professor)
            ->post(route('projects.materials.store', $project), [
                'title' => 'Curs 2',
                'material_file' => UploadedFile::fake()->create('curs2.pdf', 240),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(ProjectMaterialUploadedMail::class, function (ProjectMaterialUploadedMail $mail) use ($student, $project): bool {
            return $mail->hasTo($student->email) && $mail->project->id === $project->id;
        });

        Mail::assertNotSent(ProjectMaterialUploadedMail::class, function (ProjectMaterialUploadedMail $mail) use ($professor): bool {
            return $mail->hasTo($professor->email);
        });
    }

    public function test_retake_project_notifies_and_allows_access_only_for_failing_students(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $failingStudent = User::factory()->create(['role' => 'student']);
        $passingStudent = User::factory()->create(['role' => 'student']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa Restanta',
            'subject' => 'Analiza',
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
                'user_id' => $failingStudent->id,
                'role' => 'student',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $classroom->id,
                'user_id' => $passingStudent->id,
                'role' => 'student',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('classroom_grades')->insert([
            [
                'classroom_id' => $classroom->id,
                'student_user_id' => $failingStudent->id,
                'graded_by_user_id' => $professor->id,
                'grade_value' => 4.00,
                'feedback' => null,
                'last_warning_sent_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $classroom->id,
                'student_user_id' => $passingStudent->id,
                'graded_by_user_id' => $professor->id,
                'grade_value' => 8.00,
                'feedback' => null,
                'last_warning_sent_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($professor)
            ->post(route('projects.store'), [
                'classroom_id' => $classroom->id,
                'title' => 'Proiect recuperare',
                'description' => 'Doar pentru restantieri',
                'visibility' => 'public',
                'is_retake_project' => 1,
                'status' => 'open',
                'min_team_size' => 1,
                'max_team_size' => 2,
            ])
            ->assertRedirect(route('projects.index'));

        $project = Project::query()->where('title', 'Proiect recuperare')->first();
        $this->assertNotNull($project);

        $this->assertDatabaseHas('project_target_students', [
            'project_id' => $project->id,
            'student_user_id' => $failingStudent->id,
        ]);
        $this->assertDatabaseMissing('project_target_students', [
            'project_id' => $project->id,
            'student_user_id' => $passingStudent->id,
        ]);

        Mail::assertSent(NewProjectCreatedMail::class, function (NewProjectCreatedMail $mail) use ($failingStudent): bool {
            return $mail->hasTo($failingStudent->email);
        });
        Mail::assertNotSent(NewProjectCreatedMail::class, function (NewProjectCreatedMail $mail) use ($passingStudent): bool {
            return $mail->hasTo($passingStudent->email);
        });

        $this->actingAs($failingStudent)
            ->get(route('projects.show', $project))
            ->assertOk();
        $this->actingAs($passingStudent)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    public function test_professor_can_add_project_requirement_and_send_email(): void
    {
        Mail::fake();

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa Cerinte',
            'subject' => 'Programare Web',
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

        $project = Project::create([
            'title' => 'Portal licenta',
            'description' => 'Descriere proiect',
            'domain' => 'Programare Web',
            'classroom_id' => $classroom->id,
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 5,
            'created_by' => $professor->id,
        ]);

        $this->actingAs($professor)
            ->post(route('projects.requirements.store', $project), [
                'requirement_title' => 'Cerintele finale',
                'requirement_description' => 'Implementati autentificare, dashboard si documentatie tehnica.',
                'send_requirement_email' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('project_requirements', [
            'project_id' => $project->id,
            'title' => 'Cerintele finale',
            'version' => 1,
            'created_by' => $professor->id,
        ]);

        Mail::assertSent(ProjectRequirementAddedMail::class, function (ProjectRequirementAddedMail $mail) use ($student, $project): bool {
            return $mail->hasTo($student->email) && $mail->project->id === $project->id;
        });

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $student->id,
            'type' => 'project.requirement.added',
        ]);
    }

    public function test_english_locale_is_applied_globally_on_untranslated_pages(): void
    {
        $professor = User::factory()->create([
            'role' => 'profesor',
            'locale_preference' => 'en',
        ]);

        $this->actingAs($professor)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Platform projects')
            ->assertSee('Create project');
    }
}
