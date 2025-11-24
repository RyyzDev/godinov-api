<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PortfolioController;



Route::middleware(['auth:sanctum'])->group(function(){
	//INBOX
	Route::get('/inbox', [InboxController::class, 'index']);
	Route::get('/inbox/{id}', [InboxController::class, 'detail']);
	Route::put('/updateStatus/{id}', [InboxController::class, 'updateStatus']);
	Route::delete('/deleteInbox/{id}', [InboxController::class, 'deleteInbox']);
	Route::get('/totalDiproses', [InboxController::class, 'sumProcessed']);
	Route::get('/totalInbox', [InboxController::class, 'sumInbox']);
	Route::get('/totalKlien', [InboxController::class, 'sumClients']);

	//USER	
	Route::get('/logout', [LoginController::class, 'logout']);
	Route::get('/currentUser', [LoginController::class, 'currentUser']);

	//PORTFOLIO
	Route::post('/uploadPortfolio', [PortfolioController::class, 'store']);
	Route::get('/detailPortfolio/{id}', [PortfolioController::class, 'detail']);
	Route::get('/portfolio', [PortfolioController::class, 'index']);
	Route::put('/updateTextPortfolio/{id}', [PortfolioController::class, 'update']);
	Route::delete('/deletePortfolio/{id}', [PortfolioController::class, 'delete']);
});

	//debug cloudinary env
		// Route::get('/test-cloudinary', function() {
		//     return response()->json([
		//         'cloud_name' => config('cloudinary.cloud_name'),
		//         'api_key' => config('cloudinary.api_key'),
		//         'api_secret_exists' => !empty(config('cloudinary.api_secret')),
		//         'env_cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
		//         'env_api_key' => env('CLOUDINARY_API_KEY'),
		//     ]);
		// });	

Route::post('/inbox', [InboxController::class, 'store']);
Route::post('/login', [LoginController::class, 'authLogin']);
