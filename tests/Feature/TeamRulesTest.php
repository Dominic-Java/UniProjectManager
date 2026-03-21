<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TeamRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_can_create_team_with_student_captain_without_becoming_member(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);
        $captain = User::factory()->create(['role' => 'student']);

        $project = Project::create([
            'title' => 'Proiect Echipe',
            'description' => 'Descriere',
            'domain' => 'Programare Web',
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDay()->toDateString(),
            'end_date' => Carbon::now()->addDays(30)->toDateString(),
            'deadline_at' => Carbon::now()->addDays(10),
            'created_by' => $professor->id,
        ]);

        $this->actingAs($professor)
            ->post(route('teams.store'), [
                'project_id' => $project->id,
                'name' => 'Team Alpha',
                'status' => 'active',
                'captain_user_id' => $captain->id,
            ])
            ->assertRedirect();

        $team = Team::query()->where('project_id', $project->id)->where('name', 'Team Alpha')->first();
        $this->assertNotNull($team);

        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $captain->id,
            'role' => 'leader',
        ]);

        $this->assertDatabaseMissing('team_members', [
            'team_id' => $team->id,
            'user_id' => $professor->id,
        ]);
    }

    public function test_non_student_cannot_be_invited_in_team(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);
        $studentLeader = User::factory()->create(['role' => 'student']);
        $invitedProfessor = User::factory()->create(['role' => 'profesor']);

        $project = Project::create([
            'title' => 'Proiect Inv',
            'description' => 'Descriere',
            'domain' => 'Retele',
            'status' => 'open',
            'visibility' => 'public',
            'min_team_size' => 1,
            'max_team_size' => 4,
            'start_date' => Carbon::now()->subDay()->toDateString(),
            'end_date' => Carbon::now()->addDays(30)->toDateString(),
            'deadline_at' => Carbon::now()->addDays(10),
            'created_by' => $professor->id,
        ]);

        $team = Team::create([
            'project_id' => $project->id,
            'name' => 'Team Beta',
            'status' => 'active',
            'created_by' => $studentLeader->id,
        ]);

        \Illuminate\Support\Facades\DB::table('team_members')->insert([
            'team_id' => $team->id,
            'user_id' => $studentLeader->id,
            'role' => 'leader',
            'joined_at' => now(),
        ]);

        $this->actingAs($studentLeader)
            ->post(route('teams.invitations.send', $team), [
                'email' => $invitedProfessor->email,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('team_invitations', 0);
    }
}
