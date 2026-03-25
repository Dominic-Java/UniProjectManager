<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'code',
        'title',
        'description',
        'domain',
        'classroom_id',
        'status',
        'visibility',
        'max_team_size',
        'min_team_size',
        'start_date',
        'end_date',
        'deadline_at',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deadline_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'project_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'project_id');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class, 'project_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ProjectRequirement::class, 'project_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class, 'project_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_staff', 'project_id', 'professor_user_id')
            ->withPivot(['staff_role', 'created_at']);
    }

    public function hasDeadlinePassed(?CarbonInterface $moment = null): bool
    {
        if (!$this->deadline_at) {
            return false;
        }

        $now = $moment ?? now();

        return $this->deadline_at->lessThanOrEqualTo($now);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['closed', 'archived'], true) || $this->hasDeadlinePassed();
    }

    public function closeIfDeadlinePassed(?CarbonInterface $moment = null): bool
    {
        if (!$this->hasDeadlinePassed($moment) || in_array($this->status, ['closed', 'archived'], true)) {
            return false;
        }

        $this->status = 'archived';
        $this->save();

        return true;
    }

    public function scopeOpenForParticipation(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', ['closed', 'archived'])
            ->where(function (Builder $subQuery): void {
                $subQuery->whereNull('deadline_at')
                    ->orWhere('deadline_at', '>', now());
            });
    }
}
