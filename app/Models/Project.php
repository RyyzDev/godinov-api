<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'name',
        'description',
        'client_name',
        'service_type',
        'deadline',
        'status',
        'team_count',
    ];

    protected $casts = [
        'deadline' => 'date',
        'team_count' => 'integer',
    ];

    // Relation
    public function progress(): HasMany
    {
        return $this->hasMany(ProjectProgress::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(ProjectTimeline::class)->orderBy('order', 'asc');
    }

    public function teamMembers()
    {
        return $this->belongsToMany(TeamMember::class, 'project_team')
                    ->withPivot('role', 'joined_at', 'assigned_by') // Tambahkan assigned_by
                    ->withTimestamps();
    }

    // Helper method untuk ambil progress sebagai object
    public function getProgressAttribute()
    {
        $progressData = $this->progress()->get();
        
        return [
            'uiux' => $progressData->where('role_type', 'uiux')->first()->progress_percentage ?? 0,
            'backend' => $progressData->where('role_type', 'backend')->first()->progress_percentage ?? 0,
            'frontend' => $progressData->where('role_type', 'frontend')->first()->progress_percentage ?? 0,
        ];
    }

    // Scope for staff view (internal projects)
    public function scopeForStaff($query)
    {
        return $query->with(['progress', 'tasks']);
    }

    // Scope for client tracker view
    public function scopeForClient($query, $projectCode)
    {
        return $query->where('project_code', $projectCode)
                     ->with(['timelines']);
    }

    // Scope RAB
    public function rabSettings()
    {
        return $this->hasOne(RabProjectSettings::class);
    }

    public function capexModules()
    {
        return $this->hasMany(RabCapexModule::class);
    }

    public function opexItems()
    {
        return $this->hasMany(RabOpexItem::class);
    }

    public function revenueStreams()
    {
        return $this->hasMany(RabRevenueStream::class);
    }

    // Helper untuk hitung Total CAPEX (Cash Out T0)
    public function getTotalCapexAttribute()
    {
        return $this->capexModules->sum('total_cost');
    }
}