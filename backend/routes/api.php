<?php

use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventArtistController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventGroupController;
use App\Http\Controllers\EventMediaController;
use App\Http\Controllers\EventSessionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketTypeController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('artist-profiles')->group(function () {
    Route::get('/', [ArtistProfileController::class, 'index']);
    Route::get('/{artistProfile}', [ArtistProfileController::class, 'show']);
});

Route::prefix('groups')->group(function () {
    Route::get('/', [GroupController::class, 'index']);
    Route::get('/{group}', [GroupController::class, 'show']);
    Route::get('/{group}/members', [GroupMemberController::class, 'index']);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
});

Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{event}', [EventController::class, 'show']);
    Route::get('/{event}/sessions', [EventSessionController::class, 'index']);
    Route::get('/{event}/media', [EventMediaController::class, 'index']);
    Route::get('/{event}/artists', [EventArtistController::class, 'index']);
    Route::get('/{event}/groups', [EventGroupController::class, 'index']);
    Route::get('/{event}/sessions/{session}/ticket-types', [TicketTypeController::class, 'index']);
});

Route::prefix('venues')->group(function () {
    Route::get('/', [VenueController::class, 'index']);
    Route::get('/{venue}', [VenueController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::prefix('artist-profiles')->group(function () {
        Route::post('/', [ArtistProfileController::class, 'store']);
        Route::patch('/{artistProfile}', [ArtistProfileController::class, 'update']);
        Route::delete('/{artistProfile}', [ArtistProfileController::class, 'destroy']);
    });

    Route::prefix('groups')->group(function () {
        Route::post('/', [GroupController::class, 'store']);
        Route::patch('/{group}', [GroupController::class, 'update']);
        Route::delete('/{group}', [GroupController::class, 'destroy']);

        Route::post('/{group}/members', [GroupMemberController::class, 'store']);
        Route::patch('/{group}/members/{user}', [GroupMemberController::class, 'update']);
        Route::delete('/{group}/members/{user}', [GroupMemberController::class, 'destroy']);
    });

    Route::prefix('events')->group(function () {
        Route::post('/', [EventController::class, 'store']);
        Route::patch('/{event}', [EventController::class, 'update']);
        Route::delete('/{event}', [EventController::class, 'destroy']);

        Route::post('/{event}/sessions', [EventSessionController::class, 'store']);
        Route::patch('/{event}/sessions/{session}', [EventSessionController::class, 'update']);
        Route::delete('/{event}/sessions/{session}', [EventSessionController::class, 'destroy']);

        Route::post('/{event}/media', [EventMediaController::class, 'store']);
        Route::patch('/{event}/media/{media}', [EventMediaController::class, 'update']);
        Route::delete('/{event}/media/{media}', [EventMediaController::class, 'destroy']);

        Route::post('/{event}/artists', [EventArtistController::class, 'store']);
        Route::patch('/{event}/artists/{artistProfile}', [EventArtistController::class, 'update']);
        Route::delete('/{event}/artists/{artistProfile}', [EventArtistController::class, 'destroy']);

        Route::post('/{event}/groups', [EventGroupController::class, 'store']);
        Route::patch('/{event}/groups/{group}', [EventGroupController::class, 'update']);
        Route::delete('/{event}/groups/{group}', [EventGroupController::class, 'destroy']);

        Route::post('/{event}/sessions/{session}/ticket-types', [TicketTypeController::class, 'store']);
        Route::patch('/{event}/sessions/{session}/ticket-types/{ticketType}', [TicketTypeController::class, 'update']);
        Route::delete('/{event}/sessions/{session}/ticket-types/{ticketType}', [TicketTypeController::class, 'destroy']);

        Route::post('/{event}/sessions/{session}/ticket-types/{ticketType}/tickets', [TicketController::class, 'store']);
    });

    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/{ticket}', [TicketController::class, 'show']);
    });

    Route::middleware('admin')->group(function () {
        Route::prefix('categories')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::patch('/{category}', [CategoryController::class, 'update']);
            Route::delete('/{category}', [CategoryController::class, 'destroy']);
        });

        Route::prefix('events')->group(function () {
            Route::patch('/{event}/approve', [EventController::class, 'approve']);
            Route::patch('/{event}/reject', [EventController::class, 'reject']);
        });

        Route::prefix('venues')->group(function () {
            Route::post('/', [VenueController::class, 'store']);
            Route::patch('/{venue}', [VenueController::class, 'update']);
            Route::delete('/{venue}', [VenueController::class, 'destroy']);
        });
    });
});
