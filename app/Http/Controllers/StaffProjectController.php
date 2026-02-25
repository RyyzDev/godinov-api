<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\ProjectProgress;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Events\ProjectCreated;
use App\Events\ProjectProgressUpdated;

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
        // 1. Validasi Input
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
            // 2. Create Project Utama
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

            // =========================================================================
            // 3. LOGIKA RAB: Copy Konfigurasi Global ke Project Ini
            // =========================================================================
            
            // Ambil data dari tabel Global Settings (pastikan nama tabel sesuai migrasi)
            $global = DB::table('rab_general_settings')->first();

            // Data yang akan dimasukkan ke rab_project_settings
            // Menggunakan operator '??' sebagai fallback jika global belum diset
            $rabSettings = [
                'project_id' => $project->id,
                
                // Profil Perusahaan (Snapshot agar historis aman)
                'company_name' => $global->company_name ?? 'My Company',
                'company_address' => $global->company_address ?? '-',
                'contact_email' => $global->contact_email ?? '-',
                'prepared_by' => $global->prepared_by ?? 'Admin',

                // Parameter Keuangan
                'tax_rate' => $global->default_tax_rate ?? 11.00,
                'inflation_rate' => $global->default_inflation_rate ?? 5.00,
                'discount_rate' => $global->default_discount_rate ?? 10.00,
                'variable_cost_percentage' => $global->default_variable_cost_percentage ?? 35.00,
                'currency_code' => 'IDR',

                // Standar Manpower
                'hourly_rate' => $global->default_hourly_rate ?? 150000,
                'work_hours_per_day' => $global->work_hours_per_day ?? 8,
                'work_days_per_month' => $global->work_days_per_month ?? 22,

                //'created_at' => now(),
                //'updated_at' => now(),
            ];

            // Insert ke tabel RAB Project Settings
            DB::table('rab_project_settings')->insert($rabSettings);

            // =========================================================================

            // 4. Create Initial Progress (Logic Bawaan Anda)
            $progressData = $request->input('progress', []);
            $roles = ['uiux', 'backend', 'frontend'];
            
            foreach ($roles as $role) {
                // Menggunakan DB Facade atau Model ProjectProgress
                DB::table('project_progress')->insert([
                    'project_id' => $project->id,
                    'role_type' => $role,
                    'progress_percentage' => $progressData[$role] ?? 0,
                ]);
            }

            DB::commit();

            // 5. Load Relasi & Broadcast (Opsional)
            // $project->load(['progress']); 
            // broadcast(new ProjectCreated($project))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully with RAB configuration.',
                'data' => [
                    'id' => $project->id,
                    'project_code' => $project->project_code,
                    'name' => $project->name,
                    'client_name' => $project->client_name,
                    'status' => $project->status,
                    'has_rab_settings' => true // Flag penanda sukses
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
        $project = Project::with(['tasks' => function ($query) {
            $query->with('assigner')->orderBy('order');
         }])->findOrFail($id);

       
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
                 'tasks' => $project->tasks->map(function ($task) {
                            return [
                                'id' => $task->id,
                                'title' => $task->title,
                                'role' => $task->role,
                                'status' => $task->status,
                                'priority' => $task->priority,
                                'assignee' => $task->assignee,
                                'assigned_by_name' => $task->assigner ? $task->assigner->name : 'System',
                                'duration_seconds' => $task->duration_seconds ?? 0,
                            ];
                        }),
            ],
        ]);
    }

    public function assignMember(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        
        // Menggunakan syncWithoutDetaching agar tidak menghapus member lama
        $project->teamMembers()->syncWithoutDetaching([
            $request->team_member_id => [
                'role' => $request->role,
                'assigned_by' => auth()->id(), // OTOMATIS mengambil ID Admin yang login
                'joined_at' => now()
            ]
        ]);

        return response()->json(['message' => 'Member berhasil ditugaskan']);
    }


    /**
     * TASK
     */

        /**
     * Store Task
     */
       public function storeTask(Request $request, $id)
        {
            // 1. Cek apakah user yang login memiliki role yang diizinkan
            $allowedRoles = ['admin', 'pm'];
            $user = auth()->user();

            if (!in_array($user->role, $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Admin dan PM yang dapat membuat task baru.'
                ], 403); // 403 adalah Forbidden
            }

            $validated = $request->validate([
                'title'    => 'required|string',
                'role'     => 'required|in:uiux,backend,frontend,mobile,devops',
                'priority' => 'required|in:Low,Medium,High,Critical', 
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
                'assigned_by' => auth()->id(),
            ]);

            // broadcast(new ProjectProgressUpdated($task->project))->toOthers();

            return response()->json([
                'success' => true,
                'data'    => $task
            ], 201);
        }


    public function completedTask(Request $request, $projectId, $taskId)
    {
         $project = Project::findOrFail($projectId);
         $task = $project->tasks()->findOrFail($taskId);

        // Update data task
        $task->update([
            'status' => $request->status,
            'duration_seconds' => $request->duration,
            'completed_at' => now()
        ]);

        // Hitung ulang progress project otomatis
        $this->recalculateProgress($project);
        
        // Logika Update Status Project
        $allTasks = $project->tasks()->get();
        $totalTasks = $allTasks->count();
        
        if ($totalTasks > 0) {
            $inProgressTasksCount = $allTasks->where('status', 'In Progress')->count();
            $completedTasksCount = $allTasks->where('status', 'Done')->count(); 

            if ($completedTasksCount === $totalTasks) {
                $project->update(['status' => 'Completed']);
            } elseif ($inProgressTasksCount > 0 || $completedTasksCount > 0) {
                $project->update(['status' => 'In Progress']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Task completed and project status evaluated.',
            'data' => [
                'task' => $task,
                'project_status' => $project->fresh()->status 
            ]
        ]);
    }

    
    public function requestOtp(Request $request, $projectId, $taskId)
    {
        $task = Task::with('project')->findOrFail($taskId);
        $task->update(['status' => 'Blocked']);
        // 1. Generate OTP
        $otpCode = (string) rand(100000, 999999);

        // 2. Simpan OTP ke Cache (Key: otp_code_1)
        Cache::put("otp_code_{$taskId}", $otpCode, now()->addMinutes(10));

        // 3. Simpan Metadata ke Cache (Key: pending_otp_tasks)
        // Ini agar PM bisa me-list task mana saja yang sedang "pending" OTP
        $pendingTasks = Cache::get('pending_otp_list', []);
        $pendingTasks[$taskId] = [
            'id' => $task->id,
            'title' => $task->title,
            'project_name' => $task->project->name,
            'assignee_name' => auth()->user()->name,
            'otp_code' => $otpCode, // Simpan kodenya di sini agar PM bisa baca
            'expires_at' => now()->addMinutes(10)->toDateTimeString()
        ];
        Cache::put('pending_otp_list', $pendingTasks, now()->addMinutes(10));

        return response()->json([
            'success' => true, 
            'message' => 'OTP telah digenerate di sistem PM'
        ]);
    }

    public function getPendingApprovals(Request $request) 
    {
        $user = $request->user();
        $data = [];

        if ($user->role === 'pm' || $user->role === 'admin') {
            // Ambil daftar dari cache
            $pendingTasks = Cache::get('pending_otp_list', []);

            // Opsional: Filter jika ada yang sudah expired (pembersihan manual)
            $data = array_values(array_filter($pendingTasks, function($item) {
                return now()->parse($item['expires_at'])->isFuture();
            }));
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function verifyOtp(Request $request, $projectId, $taskId)
    {
        $inputOtp = $request->otp;
        $storedOtp = Cache::get("otp_code_{$taskId}");

        if (!$storedOtp || $inputOtp != $storedOtp) {
            return response()->json([
                'success' => false, 
                'message' => 'OTP Salah atau Kadaluwarsa',
                'debug' => [ // Tambahkan ini sementara untuk debug
                    'input' => $inputOtp,
                    'stored' => $storedOtp
                ]
            ], 422);
        }

        // 1. Update Task di Database
        $task = Task::findOrFail($taskId);
        $task->update([
            'status' => 'Done',
            'completed_at' => now()
        ]);
        //broadcast(new ProjectProgressUpdated($task->project))->toOthers();

        // 2. Hapus dari Cache agar tidak muncul lagi di list PM
        Cache::forget("otp_code_{$taskId}");
        $pendingTasks = Cache::get('pending_otp_list', []);
        unset($pendingTasks[$taskId]);
        Cache::put('pending_otp_list', $pendingTasks, now()->addMinutes(10));

        return response()->json(['success' => true, 'message' => 'Task Berhasil Diselesaikan']);
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
       //broadcast(new ProjectProgressUpdated($task->project->load('tasks')))->toOthers();

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