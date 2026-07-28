<?php

use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventSessionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/artist-profiles', [ArtistProfileController::class, 'index']);
Route::get('/artist-profiles/{artistProfile}', [ArtistProfileController::class, 'show']);

Route::get('/groups', [GroupController::class, 'index']);
Route::get('/groups/{group}', [GroupController::class, 'show']);
Route::get('/groups/{group}/members', [GroupMemberController::class, 'index']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);
Route::get('/events/{event}/sessions', [EventSessionController::class, 'index']);

Route::get('/venues', [VenueController::class, 'index']);
Route::get('/venues/{venue}', [VenueController::class, 'show']);

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

    Route::post('/events', [EventController::class, 'store']);
    Route::patch('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);

    Route::post('/events/{event}/sessions', [EventSessionController::class, 'store']);
    Route::patch('/events/{event}/sessions/{session}', [EventSessionController::class, 'update']);
    Route::delete('/events/{event}/sessions/{session}', [EventSessionController::class, 'destroy']);

    Route::middleware('admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::patch('/events/{event}/approve', [EventController::class, 'approve']);
        Route::patch('/events/{event}/reject', [EventController::class, 'reject']);

        Route::post('/venues', [VenueController::class, 'store']);
        Route::patch('/venues/{venue}', [VenueController::class, 'update']);
        Route::delete('/venues/{venue}', [VenueController::class, 'destroy']);
    });
});
