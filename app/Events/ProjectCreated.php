<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;

    public function __construct(Project $project)
    {
        $this->project = $project->load('tasks');
    }

    public function broadcastOn()
    {
        return new Channel('projects');
    }

    public function broadcastAs()
    {
        return 'ProjectCreated';
    }

   public function broadcastWith()
    {
        // Helper untuk menghitung progress (sama dengan di Controller)
        $calculateProgress = function($role) {
            $tasks = $this->project->tasks->where('role', $role);
            $total = $tasks->count();
            
            if ($total === 0) return 0;
            
            $done = $tasks->where('status', 'Done')->count();
            return round(($done / $total) * 100);
        };

        return [
            'project' => [ // Bungkus dalam key 'project' agar sesuai dengan listener React
                'id' => $this->project->id,
                'project_code' => $this->project->project_code,
                'name' => $this->project->name,
                'client_name' => $this->project->client_name,
                'deadline' => $this->project->deadline->format('Y-m-d'),
                'status' => $this->project->status,
                'team_count' => $this->project->team_count,
                'progress' => [
                    'uiux' => $calculateProgress('uiux'),
                    'backend' => $calculateProgress('backend'),
                    'frontend' => $calculateProgress('frontend'),
                ],
            ]
        ];
    }
}