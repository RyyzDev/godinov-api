<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class ProjectProgressUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;

    public function __construct(Project $project)
    {
        // Muat relasi tasks untuk menghitung ulang progress
        $this->project = $project->load('tasks');
    }

    public function broadcastOn()
    {
        // Menggunakan PrivateChannel agar hanya yang berhak yang bisa akses
        return new PrivateChannel('project.' . $this->project->id);
    }

    public function broadcastAs()
    {
        return 'ProjectProgressUpdated';
    }

   public function broadcastWith()
    {
       // Reload tasks untuk memastikan hitungan progress akurat
    $project = $this->project->load('tasks');

    $calculateProgress = function($role) use ($project) {
        $tasks = $project->tasks->where('role', $role);
        $total = $tasks->count();
        if ($total === 0) return 0;
        $done = $tasks->where('status', 'Done')->count();
        return round(($done / $total) * 100);
    };

    return [
        'project' => [
            'id' => $project->id,
            'status' => $project->status,
            'progress' => [
                'uiux' => $calculateProgress('uiux'),
                'backend' => $calculateProgress('backend'),
                'frontend' => $calculateProgress('frontend'),
            ],
            'tasks' => $project->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'role' => $task->role,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'assignee' => $task->assignee,
                    'durations_seconds' => $task->duration_seconds,
                ];
            }),
        ]
    ];
    }
}