<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverableSubmission extends Model
{
    protected $table = 'deliverable_submissions';

    protected $fillable = [
        'deliverable_id',
        'project_id',
        'student_user_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size_bytes',
        'notes',
        'submitted_at',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(Deliverable::class, 'deliverable_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }
}
