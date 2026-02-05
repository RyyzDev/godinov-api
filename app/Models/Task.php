<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Task extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'role',
        'status',
        'priority',
        'assignee',
        'assigned_by',
        'order',
        'note',
        'duration_seconds',
        'completed_at',
        'activity_log',
    ];
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status']) // Hanya catat jika kolom ini berubah
            ->logOnlyDirty()                   // Hanya simpan data yang berubah saja
            ->useLogName('task_authorization'); // Nama kategori log
    }

    protected $casts = [
        'order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
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