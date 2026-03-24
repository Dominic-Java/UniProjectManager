<?php

namespace Tests\Feature;

use App\Mail\NewProjectCreatedMail;
use App\Mail\ProjectMaterialUploadedMail;
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
}
