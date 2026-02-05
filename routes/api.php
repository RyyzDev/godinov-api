<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\StaffProjectController;
use App\Http\Controllers\ClientTrackerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'service' => 'Godinov API'
    ]);
});

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Authentication)
|--------------------------------------------------------------------------
*/

// Authentication
Route::post('/login', [AuthController::class, 'authLogin']);

// Public Inbox (Contact Form)
Route::post('/inbox', [InboxController::class, 'store']);

// Client Tracker (Public)
Route::prefix('tracker')->group(function () {
    Route::get('/verify/{projectCode}', [ClientTrackerController::class, 'verify']);
    Route::get('/info/{projectCode}', [ClientTrackerController::class, 'info']); // Optional: basic info
    Route::get('/{projectCode}', [ClientTrackerController::class, 'track']);
});

// Public Portfolio
Route::prefix('portfolio')->group(function () {
        Route::get('/', [PortfolioController::class, 'index']);
        Route::get('/{id}', [PortfolioController::class, 'detail']);
    });

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Requires Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function() {
    
    /*
    |----------------------------------------------------------------------
    | USER & AUTH
    |----------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        // Route::get('/user', [AuthController::class, 'currentUser']);
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/user-info', [AuthController::class, 'getUserInfo']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    /*
    |----------------------------------------------------------------------
    | INBOX MANAGEMENT
    |----------------------------------------------------------------------
    */
    Route::prefix('inbox')->group(function () {
        // List & Detail
        Route::get('/', [InboxController::class, 'index']);
        Route::get('/{id}', [InboxController::class, 'detail']);
        
        // Actions
        Route::put('/{id}/status', [InboxController::class, 'updateStatus']);
        Route::delete('/{id}', [InboxController::class, 'deleteInbox']);
        
        // Statistics
        Route::get('/stats/total', [InboxController::class, 'sumInbox']);
        Route::get('/stats/processed', [InboxController::class, 'sumProcessed']);
        Route::get('/stats/clients', [InboxController::class, 'sumClients']);
    });

    /*
    |----------------------------------------------------------------------
    | PORTFOLIO MANAGEMENT
    |----------------------------------------------------------------------
    */
    Route::prefix('portfolio')->group(function () {
        // CRUD Operations
        Route::post('/', [PortfolioController::class, 'store']);
        Route::put('/{id}', [PortfolioController::class, 'update']);
        Route::delete('/{id}', [PortfolioController::class, 'delete']);
    });

    // --- FITUR KHUSUS ADMIN ---
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/audit-logs', [AuditController::class, 'index']);
        // Route::patch('/users/{user}/role', [AdminController::class, 'updateRole']);
    });

    /*
    |----------------------------------------------------------------------
    | PROJECT MANAGEMENT (Staff)
    |----------------------------------------------------------------------
    */
    Route::prefix('projects')->group(function () {
        // Project CRUD
        Route::get('/', [StaffProjectController::class, 'index']);
        Route::post('/', [StaffProjectController::class, 'store']);
        Route::get('/{id}', [StaffProjectController::class, 'show']);
        Route::put('/{id}', [StaffProjectController::class, 'update']);
        Route::delete('/{id}', [StaffProjectController::class, 'destroy']);
        
        // Task Management
        Route::post('/{projectId}/tasks', [StaffProjectController::class, 'storeTask']);
        Route::patch('/{projectId}/tasks/{taskId}', [StaffProjectController::class, 'updateTask']);
        Route::post('/{projectId}/tasks/{taskId}/complete', [StaffProjectController::class, 'completedTask']);
        Route::post('/{projectId}/tasks/{taskId}/request-otp', [StaffProjectController::class, 'requestOtp']);
        Route::post('/{projectId}/tasks/{taskId}/verify-otp', [StaffProjectController::class, 'verifyOtp']);

        Route::prefix('authorizations')->group(function () {
            Route::get('/project-otp', [StaffProjectController::class, 'getPendingApprovals']);
            
            // (Opsional) Route untuk menu Finance jika sudah siap
            // Route::get('/budget', [BudgetController::class, 'index']);
            // Route::get('/reimbursements', [ReimburseController::class, 'index']);
         });
		        
        // Optional: Bulk operations
        // Route::post('/bulk-delete', [StaffProjectController::class, 'bulkDestroy']);
        // Route::get('/export', [StaffProjectController::class, 'export']);
    });

});

/*
|--------------------------------------------------------------------------
| DEVELOPMENT/DEBUG ROUTES
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    
    // Test Cloudinary Configuration
    Route::get('/test-cloudinary', function() {
        return response()->json([
            'cloud_name' => config('cloudinary.cloud_name'),
            'api_key' => config('cloudinary.api_key'),
            'api_secret_exists' => !empty(config('cloudinary.api_secret')),
            'env_cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'env_api_key' => env('CLOUDINARY_API_KEY'),
        ]);
    });
    
    // Test routes without auth (for development)
    Route::get('/test/projects', [StaffProjectController::class, 'index']);
    Route::get('/test/projects/{id}', [StaffProjectController::class, 'show']);
    
}