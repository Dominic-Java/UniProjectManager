<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProjectDeadlineLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['uniprojectmanager.expired_project_retention_hours' => 24]);

        $this->withoutMiddleware([
            VerifyCsrfToken::class,
            ValidateCsrfToken::class,
        ]);
    }

    public function test_project_is_automatically_closed_after_deadline(): void
    {
        $this->assertSame('sqlite', config('database.default'));

        $professor = User::factory()->create(['role' => 'profesor']);

        $project = Project::create([
            'title' => 'Proiect Deadline',
            'description' => 'Descriere',
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDays(10)->toDateString(),
            'end_date' => Carbon::now()->addDays(10)->toDateString(),
            'deadline_at' => Carbon::now()->subMinute(),
            'created_by' => $professor->id,
        ]);

        $this->actingAs($professor)
            ->get(route('projects.index'))
            ->assertOk();

        $this->assertSame('closed', $project->fresh()->status);
    }

    public function test_project_is_automatically_deleted_after_retention_window(): void
    {
        config(['uniprojectmanager.expired_project_retention_hours' => 24]);

        $professor = User::factory()->create(['role' => 'profesor']);

        $project = Project::create([
            'title' => 'Proiect pentru stergere',
            'description' => 'Descriere',
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDays(10)->toDateString(),
            'end_date' => Carbon::now()->addDays(10)->toDateString(),
            'deadline_at' => Carbon::now()->subHours(25),
            'created_by' => $professor->id,
        ]);

        $this->actingAs($professor)
            ->get(route('projects.index'))
            ->assertOk();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_professor_can_create_project_using_24h_deadline_fields(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa Deadline',
            'subject' => 'Baze de date',
            'created_by' => $professor->id,
            'is_active' => true,
        ]);

        $this->actingAs($professor)
            ->post(route('projects.store'), [
                'classroom_id' => $classroom->id,
                'title' => 'Proiect 24h',
                'description' => 'Descriere proiect',
                'start_date' => Carbon::now()->toDateString(),
                'deadline_date' => Carbon::now()->addDays(2)->toDateString(),
                'deadline_time' => '23:15',
            ])
            ->assertRedirect(route('projects.index'));

        $project = Project::query()->where('title', 'Proiect 24h')->first();
        $this->assertNotNull($project);
        $this->assertSame('23:15', $project->deadline_at?->format('H:i'));
    }

    public function test_project_creation_rejects_non_24h_deadline_time_format(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);

        $classroom = Classroom::create([
            'code' => Classroom::generateCode(),
            'name' => 'Clasa Deadline Invalid',
            'subject' => 'Retele',
            'created_by' => $professor->id,
            'is_active' => true,
        ]);

        $this->actingAs($professor)
            ->post(route('projects.store'), [
                'classroom_id' => $classroom->id,
                'title' => 'Proiect invalid time',
                'description' => 'Descriere proiect',
                'start_date' => Carbon::now()->toDateString(),
                'deadline_date' => Carbon::now()->addDay()->toDateString(),
                'deadline_time' => '11:15 PM',
            ])
            ->assertSessionHasErrors('deadline_time');
    }

    public function test_cannot_send_team_invitation_when_project_is_closed_by_deadline(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);
        $student = User::factory()->create(['role' => 'student']);

        $project = Project::create([
            'title' => 'Proiect Inchis',
            'description' => 'Descriere',
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDays(10)->toDateString(),
            'end_date' => Carbon::now()->addDays(10)->toDateString(),
            'deadline_at' => Carbon::now()->subMinute(),
            'created_by' => $professor->id,
        ]);

        $team = Team::create([
            'project_id' => $project->id,
            'name' => 'Echipa A',
            'status' => 'active',
            'created_by' => $professor->id,
        ]);

        $this->actingAs($professor)
            ->post(route('teams.invitations.send', $team), [
                'email' => $student->email,
                'message' => 'Hai in echipa',
            ])
            ->assertRedirect();

        $this->assertSame('closed', $project->fresh()->status);
        $this->assertDatabaseCount('team_invitations', 0);
    }

    public function test_student_cannot_access_milestones_create_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('milestones.create'))
            ->assertForbidden();
    }

    public function test_projects_close_expired_command_closes_only_expired_open_projects(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);

        $expiredOpen = Project::create([
            'title' => 'Proiect expirat open',
            'description' => 'Descriere',
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDays(20)->toDateString(),
            'end_date' => Carbon::now()->addDays(5)->toDateString(),
            'deadline_at' => Carbon::now()->subMinute(),
            'created_by' => $professor->id,
        ]);

        $futureOpen = Project::create([
            'title' => 'Proiect activ',
            'description' => 'Descriere',
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(20)->toDateString(),
            'deadline_at' => Carbon::now()->addHour(),
            'created_by' => $professor->id,
        ]);

        $expiredArchived = Project::create([
            'title' => 'Proiect arhivat',
            'description' => 'Descriere',
            'status' => 'archived',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDays(30)->toDateString(),
            'end_date' => Carbon::now()->subDays(1)->toDateString(),
            'deadline_at' => Carbon::now()->subDay(),
            'created_by' => $professor->id,
        ]);

        $this->artisan('projects:close-expired')
            ->assertSuccessful();

        $this->assertSame('closed', $expiredOpen->fresh()->status);
        $this->assertSame('open', $futureOpen->fresh()->status);
        $this->assertSame('archived', $expiredArchived->fresh()->status);
    }

    public function test_professor_cannot_update_milestone_after_project_deadline(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);

        $project = Project::create([
            'title' => 'Proiect Milestone',
            'description' => 'Descriere',
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDays(10)->toDateString(),
            'end_date' => Carbon::now()->addDays(10)->toDateString(),
            'deadline_at' => Carbon::now()->subMinute(),
            'created_by' => $professor->id,
        ]);

        $milestone = Milestone::create([
            'project_id' => $project->id,
            'title' => 'Milestone initial',
            'description' => 'Descriere',
            'due_at' => Carbon::now()->addDay(),
            'weight' => 25,
            'created_by' => $professor->id,
        ]);

        $this->actingAs($professor)
            ->put(route('milestones.update', $milestone), [
                'project_id' => $project->id,
                'title' => 'Milestone modificat',
                'description' => 'Descriere noua',
                'due_at' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
                'weight' => 30,
            ])
            ->assertRedirect();

        $this->assertSame('closed', $project->fresh()->status);
        $this->assertDatabaseHas('milestones', [
            'id' => $milestone->id,
            'title' => 'Milestone initial',
        ]);
    }
}
