<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffProjectController extends Controller
{
    /**
     * Get all projects for staff dashboard
     */
          public function index()
        {
            $projects = Project::with(['progress', 'tasks'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($project) {
                    //  Fungsi Helper untuk kalkulasi progress secara dinamis
                    $calculateProgress = function($role) use ($project) {
                        $tasks = $project->tasks->where('role', $role);
                        $total = $tasks->count();
                        
                        if ($total === 0) return 0;
                        
                        $done = $tasks->where('status', 'Done')->count();
                        return round(($done / $total) * 100);
                    };

                    //  Return struktur data yang bersih
                    return [
                        'id' => $project->id,
                        'project_code' => $project->project_code,
                        'name' => $project->name,
                        'client_name' => $project->client_name,
                        'deadline' => $project->deadline->format('Y-m-d'),
                        'status' => $project->status,
                        'team_count' => $project->team_count,
                        
                        // Progress dihitung langsung dari relasi tasks
                        'progress' => [
                            'uiux' => $calculateProgress('uiux'),
                            'backend' => $calculateProgress('backend'),
                            'frontend' => $calculateProgress('frontend'),
                        ],
                    ];
                }); 

            return response()->json([
                'success' => true,
                'data' => $projects,
            ]);
        }

    /**
     * Create new project
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'project_code' => 'required|string|max:50|unique:projects,project_code',
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'client_name' => 'required|string|max:255',
        'service_type' => 'required|string|max:255',
        'deadline' => 'required|date|after_or_equal:today',
        'status' => 'nullable|in:Planning,In Progress,Review,Completed,On Hold',
        'team_count' => 'nullable|integer|min:0',
        'progress' => 'nullable|array',
        'progress.uiux' => 'nullable|integer|min:0|max:100',
        'progress.backend' => 'nullable|integer|min:0|max:100',
        'progress.frontend' => 'nullable|integer|min:0|max:100',
    ]);

    DB::beginTransaction();

    try {
        // 1. Create project
        $project = Project::create([
            'project_code' => strtoupper($validated['project_code']),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'client_name' => $validated['client_name'],
            'service_type' => $validated['service_type'],
            'deadline' => $validated['deadline'],
            'status' => $validated['status'] ?? 'Planning',
            'team_count' => $validated['team_count'] ?? 0,
        ]);

        // 2. Create initial progress
        $progressData = $request->input('progress', []);
        foreach (['uiux', 'backend', 'frontend'] as $role) {
            ProjectProgress::create([
                'project_id' => $project->id,
                'role_type' => $role,
                'progress_percentage' => $progressData[$role] ?? 0,
            ]);
        }

     

        DB::commit();

        // Load relationships untuk response
        $project->load(['progress', 'timelines']);

        return response()->json([
            'success' => true,
            'message' => 'Project and Timeline created successfully',
            'data' => [
                'id' => $project->id,
                'project_code' => $project->project_code,
                'name' => $project->name,
                'status' => $project->status,
                'progress' => [
                   'uiux' => collect($project->progress)->where('role_type', 'uiux')->first()->progress_percentage ?? 0,
                    'backend' => collect($project->progress)->where('role_type', 'backend')->first()->progress_percentage ?? 0,
                    'frontend' => collect($project->progress)->where('role_type', 'frontend')->first()->progress_percentage ?? 0,
                ],
            ],
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to create project',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Get single project detail for staff
     */
    public function show($id)
    {
        $project = Project::with(['progress', 'tasks' => function ($query) {
            $query->orderBy('order');
        }])
            ->findOrFail($id);

         $calculateProgress = function($role) use ($project) {
             $tasks = $project->tasks->where('role', $role);
            $total = $tasks->count();
                        
              if ($total === 0) return 0;
                        
            $done = $tasks->where('status', 'Done')->count();
            return round(($done / $total) * 100);
                    };


        return response()->json([
            'success' => true,
            'data' => [
                'id' => $project->id,
                'project_code' => $project->project_code,
                'name' => $project->name,
                'description' => $project->description,
                'client_name' => $project->client_name,
                'service_type' => $project->service_type,
                'deadline' => $project->deadline->format('Y-m-d'),
                'status' => $project->status,
                'team_count' => $project->team_count,
                // Progress dihitung langsung dari relasi tasks
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
                            ];
                        }),
            ],
        ]);
    }


    /**
     * TASK
     */

        /**
     * Store Task
     */
       public function storeTask(Request $request, $id)
        {
            $validated = $request->validate([
                'title'    => 'required|string',
                'role'     => 'required|in:uiux,backend,frontend,mobile,devops',
                'priority' => 'required|in:Low,Medium,High,Critical', // Sesuaikan case-sensitive
                'assignee' => 'required|string',
                'note'     => 'nullable',
            ]);

            $project = Project::findOrFail($id);
            
            $task = $project->tasks()->create([
                'title'    => $validated['title'],
                'role'     => $validated['role'],
                'priority' => $validated['priority'],
                'assignee' => $validated['assignee'],
                'status'   => 'Todo',
                'note'     => $validated['note'], 
                'order'    => $project->tasks()->count() + 1,
            ]);

            return response()->json([
                'success' => true,
                'data'    => $task
            ], 201);
        }

        /**
         * Update task status
         */
    public function updateTask(Request $request, $projectId, $taskId)
    {
        $validated = $request->validate([
            'status' => 'required|in:Todo,In Progress,Done,Blocked',
        ]);

        $project = Project::findOrFail($projectId);
        $task = $project->tasks()->findOrFail($taskId);
        
        $task->update($validated);

        // Auto-calculate progress based on completed tasks
        $this->recalculateProgress($project);

        // Reload project with updated progress
        $project->load(['progress', 'tasks']);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => [
                'task' => $task,
                'progress' => [
                    'uiux' => collect($project->progress)->where('role_type', 'uiux')->first()->progress_percentage ?? 0,
                    'backend' => collect($project->progress)->where('role_type', 'backend')->first()->progress_percentage ?? 0,
                    'frontend' => collect($project->progress)->where('role_type', 'frontend')->first()->progress_percentage ?? 0,
                ],
            ],
        ]);
    }

    /**
     * Recalculate project progress based on tasks
     */
    private function recalculateProgress(Project $project)
    {
        foreach (['uiux', 'backend', 'frontend'] as $role) {
            $totalTasks = $project->tasks()->where('role', $role)->count();
            
            if ($totalTasks > 0) {
                $completedTasks = $project->tasks()
                    ->where('role', $role)
                    ->where('status', 'Done')
                    ->count();
                
                $percentage = round(($completedTasks / $totalTasks) * 100);
                
                $project->progress()->updateOrCreate(
                    ['role_type' => $role],
                    ['progress_percentage' => $percentage]
                );
            }
        }
    }

    /**
     * Update project details
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'client_name' => 'sometimes|string|max:255',
            'service_type' => 'sometimes|string|max:255',
            'deadline' => 'sometimes|date',
            'status' => 'sometimes|in:Planning,In Progress,Review,Completed,On Hold',
            'team_count' => 'sometimes|integer|min:0',
        ]);

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => $project,
        ]);
    }

    /**
     * Delete project
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }
}