<?php

namespace Tests\Feature;

use App\Mail\NewProjectCreatedMail;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
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

        $this->actingAs($professor)
            ->post(route('projects.store'), [
                'title' => 'Proiect Final',
                'description' => 'Descriere proiect',
                'domain' => 'Algoritmi',
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

        $project = Project::create([
            'title' => 'Classwork OOP',
            'description' => 'Descriere',
            'domain' => 'Programare Orientata pe Obiect',
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
}
