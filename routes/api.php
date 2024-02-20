<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;


Broadcast::routes([
    'middleware' => ['auth:sanctum']
]);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->as('auth')->group(function() {
    Route::post('login', [AuthController::class, 'login'])->name('name');
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login_with_token', [AuthController::class, 'loginWithToken'])
        ->middleware('auth:sanctum')
        ->name('login_with_token');
    Route::get('logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('logout');
});

Route::middleware('auth:sanctum')->group(function() {
    Route::apiResource('chat', ChatController::class)->only(['index', 'store', 'show']);
    Route::apiResource('chat_message', ChatMessageController::class)->only(['index', 'store']);
    Route::apiResource('user', UserController::class)->only(['index']);
    // Route::get('profile', [ProfileController::class, 'index']);
    Route::controller(ProfileController::class)->group(function(){
        Route::post('profile-bio', 'updateBio');
        Route::post('profile-username', 'updateUsername');
        Route::post('profile-avatar', 'updateAvatar');
        Route::post('profile-phones', 'updateNumber');
    });
    // Route::post('profile-username', [ProfileController::class, 'updateUsername']);
    // Route::post('profile-bio', [ProfileController::class, 'updateBio']);
    // Route::post('profile-phones', [ProfileController::class, 'updateNumber']);
    // Route::post('profile-avatar', [ProfileController::class, 'updateAvatar']);
});
