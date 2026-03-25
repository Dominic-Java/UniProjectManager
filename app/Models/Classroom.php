<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Classroom extends Model
{
    protected $table = 'classrooms';

    protected $fillable = [
        'code',
        'name',
        'subject',
        'description',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'classroom_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_members', 'classroom_id', 'user_id')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ClassroomInvitation::class, 'classroom_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(ClassroomGrade::class, 'classroom_id');
    }

    public static function generateCode(): string
    {
        do {
            $code = 'CLS-' . Str::upper(Str::random(8));
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }
}
