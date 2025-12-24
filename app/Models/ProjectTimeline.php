<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTimeline extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'date',
        'status',
        'note',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Scope untuk current step
    public function scopeCurrent($query)
    {
        return $query->where('status', 'current');
    }

    // Scope untuk completed steps
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}

