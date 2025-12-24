<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProgress extends Model
{
    public $timestamps = false;

    protected $table = 'project_progress';

    protected $fillable = [
        'project_id',
        'role_type',
        'progress_percentage',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}