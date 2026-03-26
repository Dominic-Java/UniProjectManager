<?php

namespace App\Support;

use App\Models\Classroom;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClassroomAccess
{
    public static function canManageClassroom(?User $user, Classroom $classroom): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (!$user->hasRole('profesor')) {
            return false;
        }

        return (int) $classroom->created_by === (int) $user->id;
    }

    public static function canAccessClassroom(?User $user, Classroom $classroom): bool
    {
        if (!$user) {
            return false;
        }

        if (self::canManageClassroom($user, $classroom)) {
            return true;
        }

        if ($user->hasRole('profesor')) {
            return false;
        }

        return DB::table('classroom_members')
            ->where('classroom_id', $classroom->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public static function canManageProject(?User $user, Project $project): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (!$user->hasRole('profesor')) {
            return false;
        }

        if ($project->classroom_id) {
            $project->loadMissing('classroom');
            return (int) $project->classroom?->created_by === (int) $user->id;
        }

        return (int) $project->created_by === (int) $user->id;
    }

    public static function canUploadClasswork(?User $user, Project $project): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (!$user->hasRole('profesor')) {
            return false;
        }

        if (self::canManageProject($user, $project)) {
            return true;
        }

        if (
            Schema::hasTable('project_staff')
            && DB::table('project_staff')
                ->where('project_id', $project->id)
                ->where('professor_user_id', $user->id)
                ->exists()
        ) {
            return true;
        }

        return self::isClassroomTeacher($user, $project);
    }

    public static function canAccessProject(?User $user, Project $project): bool
    {
        if (!$user) {
            return false;
        }

        if (self::canManageProject($user, $project)) {
            return true;
        }

        if ($user->hasRole('profesor')) {
            return self::canUploadClasswork($user, $project);
        }

        if ($project->classroom_id) {
            $isMember = DB::table('classroom_members')
                ->where('classroom_id', $project->classroom_id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isMember) {
                return false;
            }

            if (self::isRetakeProject($project)) {
                return DB::table('project_target_students')
                    ->where('project_id', $project->id)
                    ->where('student_user_id', $user->id)
                    ->exists();
            }

            return true;
        }

        return DB::table('team_members')
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->where('teams.project_id', $project->id)
            ->where('team_members.user_id', $user->id)
            ->exists();
    }

    public static function scopeVisibleProjects(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->hasRole('profesor')) {
            return self::scopeTeachableProjects($query, $user);
        }

        return $query->where(function (Builder $subQuery) use ($user): void {
            $subQuery->where(function (Builder $classroomQuery) use ($user): void {
                $classroomQuery
                    ->whereNotNull('classroom_id')
                    ->whereHas('classroom.members', fn (Builder $memberQuery) => $memberQuery->where('users.id', $user->id));

                if (Schema::hasColumn('projects', 'is_retake_project') && Schema::hasTable('project_target_students')) {
                    $classroomQuery->where(function (Builder $audienceQuery) use ($user): void {
                        $audienceQuery
                            ->where('is_retake_project', false)
                            ->orWhereNull('is_retake_project')
                            ->orWhereExists(function ($existsQuery) use ($user): void {
                                $existsQuery->selectRaw('1')
                                    ->from('project_target_students')
                                    ->whereColumn('project_target_students.project_id', 'projects.id')
                                    ->where('project_target_students.student_user_id', $user->id);
                            });
                    });
                }
            })->orWhere(function (Builder $legacyQuery) use ($user): void {
                $legacyQuery
                    ->whereNull('classroom_id')
                    ->whereHas('teams.members', fn (Builder $memberQuery) => $memberQuery->where('users.id', $user->id));
            });
        });
    }

    public static function scopeManageableProjects(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($user): void {
            $subQuery->where(function (Builder $classroomQuery) use ($user): void {
                $classroomQuery
                    ->whereNotNull('classroom_id')
                    ->whereHas('classroom', fn (Builder $classroomBuilder) => $classroomBuilder->where('created_by', $user->id));
            })->orWhere(function (Builder $legacyQuery) use ($user): void {
                $legacyQuery
                    ->whereNull('classroom_id')
                    ->where('created_by', $user->id);
                });
        });
    }

    public static function scopeTeachableProjects(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($user): void {
            self::scopeManageableProjects($subQuery, $user)
                ->orWhere(function (Builder $staffQuery) use ($user): void {
                    if (!Schema::hasTable('project_staff')) {
                        $staffQuery->whereRaw('1 = 0');
                        return;
                    }

                    $staffQuery->whereHas('staff', fn (Builder $staffBuilder) => $staffBuilder->where('users.id', $user->id));
                })
                ->orWhere(function (Builder $teacherQuery) use ($user): void {
                    $teacherQuery
                        ->whereNotNull('classroom_id')
                        ->whereHas('classroom.members', function (Builder $memberBuilder) use ($user): void {
                            $memberBuilder
                                ->where('users.id', $user->id)
                                ->where('classroom_members.role', 'teacher');
                        });
                });
        });
    }

    private static function isClassroomTeacher(User $user, Project $project): bool
    {
        if (!$project->classroom_id || !Schema::hasTable('classroom_members')) {
            return false;
        }

        return DB::table('classroom_members')
            ->where('classroom_id', $project->classroom_id)
            ->where('user_id', $user->id)
            ->where('role', 'teacher')
            ->exists();
    }

    private static function isRetakeProject(Project $project): bool
    {
        if (!Schema::hasColumn('projects', 'is_retake_project') || !Schema::hasTable('project_target_students')) {
            return false;
        }

        return (bool) $project->is_retake_project;
    }
}
