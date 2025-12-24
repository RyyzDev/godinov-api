<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'role',
        'status',
        'priority',
        'assignee',
        'order',
        'note',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Scope untuk filter by status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk filter by role
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Scope untuk filter by assignee
    public function scopeAssignedTo($query, $assignee)
    {
        return $query->where('assignee', $assignee);
    }
}