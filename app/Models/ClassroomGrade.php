<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomGrade extends Model
{
    protected $table = 'classroom_grades';

    protected $fillable = [
        'classroom_id',
        'student_user_id',
        'graded_by_user_id',
        'grade_value',
        'feedback',
        'last_warning_sent_at',
    ];

    protected $casts = [
        'grade_value' => 'decimal:2',
        'feedback' => 'encrypted',
        'last_warning_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by_user_id');
    }

    public function isFailing(): bool
    {
        return (float) $this->grade_value < 5.0;
    }
}
