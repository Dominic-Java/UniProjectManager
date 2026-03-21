<?php

namespace Tests\Feature;

use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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

        $project = Project::create([
            'title' => 'Proiect Fisiere',
            'description' => 'Descriere',
            'domain' => 'Baze de date',
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
    }

    public function test_student_cannot_upload_when_project_is_locked(): void
    {
        Storage::fake('local');

        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);

        $project = Project::create([
            'title' => 'Proiect Inchis',
            'description' => 'Descriere',
            'domain' => 'IA',
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

        $project = Project::create([
            'title' => 'Proiect Reupload',
            'description' => 'Descriere',
            'domain' => 'PAOO',
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
}
