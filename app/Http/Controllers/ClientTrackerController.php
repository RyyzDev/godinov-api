<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ClientTrackerController extends Controller
{
    /**
     * Get project tracking info by project code
     * This is the public-facing endpoint for clients
     */
   public function track($projectCode)
{
    try {
        $project = Project::where('project_code', strtoupper($projectCode))
            ->with(['tasks' => function ($query) {
                $query->orderBy('created_at', 'desc'); 
            }])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $project->project_code,
                'client' => $project->client_name,
                'service' => $project->service_type,
                'status' => $project->status,
                'timeline' => $project->tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'date' => $task->created_at ? $task->created_at->format('d M Y') : 'Pending',
                        'status' => $this->mapTaskStatusToTimeline($task->status),
                        'note' => $task->note ?? '-', 
                    ];
                }),
            ],
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Project dengan kode "' . $projectCode . '" tidak ditemukan.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengambil data project.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    private function mapTaskStatusToTimeline($status)
{
    switch ($status) {
        case 'Done':
            return 'completed';
        case 'In Progress':
            return 'current';
        default:
            return 'pending';
    }
}

    /**
     * Verify project code exists
     */
    public function verify($projectCode)
    {
        $exists = Project::where('project_code', strtoupper($projectCode))->exists();

        return response()->json([
            'success' => true,
            'exists' => $exists,
            'project_code' => strtoupper($projectCode),
        ]);
    }

    /**
     * Get basic project info without timeline (lighter response)
     */
    public function info($projectCode)
    {
        try {
            $project = Project::where('project_code', strtoupper($projectCode))
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $project->project_code,
                    'name' => $project->name,
                    'client' => $project->client_name,
                    'service' => $project->service_type,
                    'status' => $project->status,
                    'deadline' => $project->deadline->format('d M Y'),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project tidak ditemukan.',
            ], 404);
        }
    }
}