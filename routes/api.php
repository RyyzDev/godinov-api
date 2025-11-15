<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\LoginController;




Route::middleware(['auth:sanctum'])->group(function(){
Route::get('/inbox', [InboxController::class, 'index']);
Route::get('/inbox/{id}', [InboxController::class, 'detail']);
Route::put('/updateStatus/{id}', [InboxController::class, 'updateStatus']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::get('/currentUser', [LoginController::class, 'currentUser']);
Route::delete('/deleteInbox/{id}', [InboxController::class, 'deleteInbox']);

//api baru sebelum deploy
Route::get('/totalDiproses', [InboxController::class, 'sumProcessed']);
Route::get('/totalInbox', [InboxController::class, 'sumInbox']);
Route::get('/totalKlien', [InboxController::class, 'sumClients']);
});



Route::post('/inbox', [InboxController::class, 'store']);
Route::post('/login', [LoginController::class, 'authLogin']);
