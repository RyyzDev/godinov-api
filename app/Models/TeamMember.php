<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'role',
        'avatar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_team')
                    ->withPivot('role', 'joined_at');
    }

    // Scope untuk active members
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk filter by role
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }
}