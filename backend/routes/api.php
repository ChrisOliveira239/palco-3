<?php

use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/artist-profiles', [ArtistProfileController::class, 'index']);
Route::get('/artist-profiles/{artistProfile}', [ArtistProfileController::class, 'show']);

Route::get('/groups', [GroupController::class, 'index']);
Route::get('/groups/{group}', [GroupController::class, 'show']);
Route::get('/groups/{group}/members', [GroupMemberController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::post('/artist-profiles', [ArtistProfileController::class, 'store']);
    Route::patch('/artist-profiles/{artistProfile}', [ArtistProfileController::class, 'update']);
    Route::delete('/artist-profiles/{artistProfile}', [ArtistProfileController::class, 'destroy']);

    Route::post('/groups', [GroupController::class, 'store']);
    Route::patch('/groups/{group}', [GroupController::class, 'update']);
    Route::delete('/groups/{group}', [GroupController::class, 'destroy']);

    Route::post('/groups/{group}/members', [GroupMemberController::class, 'store']);
    Route::patch('/groups/{group}/members/{user}', [GroupMemberController::class, 'update']);
    Route::delete('/groups/{group}/members/{user}', [GroupMemberController::class, 'destroy']);
});
