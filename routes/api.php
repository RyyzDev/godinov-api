<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
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

Route::post('/login', [AuthController::class, 'authLogin']);
Route::post('/inbox', [InboxController::class, 'store']);

Route::prefix('tracker')->group(function () {
    Route::get('/verify/{projectCode}', [ClientTrackerController::class, 'verify']);
    Route::get('/info/{projectCode}', [ClientTrackerController::class, 'info']);
    Route::get('/{projectCode}', [ClientTrackerController::class, 'track']);
});

Route::prefix('portfolio')->group(function () {
    Route::get('/', [PortfolioController::class, 'index']);
    Route::get('/{id}', [PortfolioController::class, 'detail']);
});

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Requires Authentication)
|--------------------------------------------------------------------------
*/

// 1.Route Broadcasting untuk API Standalone
Broadcast::routes(['middleware' => ['auth:sanctum']]);

// 2. Perbaikan Grup Middleware Sanctum
Route::middleware(['auth:sanctum'])->group(function() {
    
    /* USER & AUTH */
    Route::prefix('auth')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/user-info', [AuthController::class, 'getUserInfo']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    /* INBOX MANAGEMENT */
    Route::prefix('inbox')->group(function () {
        Route::get('/', [InboxController::class, 'index']);
        Route::get('/{id}', [InboxController::class, 'detail']);
        Route::put('/{id}/status', [InboxController::class, 'updateStatus']);
        Route::delete('/{id}', [InboxController::class, 'deleteInbox']);
        Route::get('/stats/total', [InboxController::class, 'sumInbox']);
        Route::get('/stats/processed', [InboxController::class, 'sumProcessed']);
        Route::get('/stats/clients', [InboxController::class, 'sumClients']);
    });

    /* PORTFOLIO MANAGEMENT */
    Route::prefix('portfolio')->group(function () {
        Route::post('/', [PortfolioController::class, 'store']);
        Route::put('/{id}', [PortfolioController::class, 'update']);
        Route::delete('/{id}', [PortfolioController::class, 'delete']);
    });

    /* ADMIN ONLY */
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/audit-logs', [AuditController::class, 'index']);
    });

    /* PROJECT MANAGEMENT */
    Route::prefix('projects')->group(function () {
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
         });
    });
}); // <--- Penutup middleware auth:sanctum yang benar

/*
|--------------------------------------------------------------------------
| DEVELOPMENT/DEBUG ROUTES
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::get('/test-cloudinary', function() {
        return response()->json([
            'cloud_name' => config('cloudinary.cloud_name'),
            'api_key' => config('cloudinary.api_key'),
            'api_secret_exists' => !empty(config('cloudinary.api_secret')),
        ]);
    });
    
    Route::get('/test/projects', [StaffProjectController::class, 'index']);
    Route::get('/test/projects/{id}', [StaffProjectController::class, 'show']);
}