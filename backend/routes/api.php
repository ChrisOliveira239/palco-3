<?php

use App\Http\Controllers\AcceptedSupportTypeController;
use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventArtistController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventGroupController;
use App\Http\Controllers\EventMediaController;
use App\Http\Controllers\EventSessionController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FeedPostController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\OpportunityApplicationController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SponsorshipController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketTypeController;
use App\Http\Controllers\TicketValidationController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('artist-profiles')->group(function () {
    Route::get('/', [ArtistProfileController::class, 'index']);
    Route::get('/{artistProfile}', [ArtistProfileController::class, 'show']);
    Route::get('/{artistProfile}/accepted-support-types', [AcceptedSupportTypeController::class, 'indexForArtistProfile']);
    Route::get('/{artistProfile}/sponsorships', [SponsorshipController::class, 'indexForArtistProfile']);
    Route::get('/{artistProfile}/followers', [FollowController::class, 'followersForArtistProfile']);
    Route::get('/{artistProfile}/feed-posts', [FeedPostController::class, 'indexForArtistProfile']);
});

Route::prefix('groups')->group(function () {
    Route::get('/', [GroupController::class, 'index']);
    Route::get('/{group}', [GroupController::class, 'show']);
    Route::get('/{group}/members', [GroupMemberController::class, 'index']);
    Route::get('/{group}/accepted-support-types', [AcceptedSupportTypeController::class, 'indexForGroup']);
    Route::get('/{group}/sponsorships', [SponsorshipController::class, 'indexForGroup']);
    Route::get('/{group}/followers', [FollowController::class, 'followersForGroup']);
    Route::get('/{group}/feed-posts', [FeedPostController::class, 'indexForGroup']);
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
    Route::get('/{event}/accepted-support-types', [AcceptedSupportTypeController::class, 'indexForEvent']);
    Route::get('/{event}/sponsorships', [SponsorshipController::class, 'indexForEvent']);
    Route::get('/{event}/followers', [FollowController::class, 'followersForEvent']);
});

Route::prefix('venues')->group(function () {
    Route::get('/', [VenueController::class, 'index']);
    Route::get('/{venue}', [VenueController::class, 'show']);
});

Route::prefix('opportunities')->group(function () {
    Route::get('/', [OpportunityController::class, 'index']);
    Route::get('/{opportunity}', [OpportunityController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::prefix('artist-profiles')->group(function () {
        Route::post('/', [ArtistProfileController::class, 'store']);
        Route::patch('/{artistProfile}', [ArtistProfileController::class, 'update']);
        Route::delete('/{artistProfile}', [ArtistProfileController::class, 'destroy']);

        Route::post('/{artistProfile}/accepted-support-types', [AcceptedSupportTypeController::class, 'storeForArtistProfile']);
        Route::delete('/{artistProfile}/accepted-support-types/{acceptedSupportType}', [AcceptedSupportTypeController::class, 'destroyForArtistProfile']);

        Route::get('/{artistProfile}/sponsorships/pending', [SponsorshipController::class, 'pendingForArtistProfile']);
        Route::post('/{artistProfile}/sponsorships', [SponsorshipController::class, 'storeForArtistProfile']);

        Route::post('/{artistProfile}/follow', [FollowController::class, 'followArtistProfile']);
        Route::delete('/{artistProfile}/follow', [FollowController::class, 'unfollowArtistProfile']);

        Route::post('/{artistProfile}/feed-posts', [FeedPostController::class, 'storeForArtistProfile']);
        Route::patch('/{artistProfile}/feed-posts/{feedPost}', [FeedPostController::class, 'updateForArtistProfile']);
        Route::delete('/{artistProfile}/feed-posts/{feedPost}', [FeedPostController::class, 'destroyForArtistProfile']);
    });

    Route::prefix('groups')->group(function () {
        Route::post('/', [GroupController::class, 'store']);
        Route::patch('/{group}', [GroupController::class, 'update']);
        Route::delete('/{group}', [GroupController::class, 'destroy']);

        Route::post('/{group}/members', [GroupMemberController::class, 'store']);
        Route::patch('/{group}/members/{user}', [GroupMemberController::class, 'update']);
        Route::delete('/{group}/members/{user}', [GroupMemberController::class, 'destroy']);

        Route::post('/{group}/accepted-support-types', [AcceptedSupportTypeController::class, 'storeForGroup']);
        Route::delete('/{group}/accepted-support-types/{acceptedSupportType}', [AcceptedSupportTypeController::class, 'destroyForGroup']);

        Route::get('/{group}/sponsorships/pending', [SponsorshipController::class, 'pendingForGroup']);
        Route::post('/{group}/sponsorships', [SponsorshipController::class, 'storeForGroup']);

        Route::post('/{group}/follow', [FollowController::class, 'followGroup']);
        Route::delete('/{group}/follow', [FollowController::class, 'unfollowGroup']);

        Route::post('/{group}/feed-posts', [FeedPostController::class, 'storeForGroup']);
        Route::patch('/{group}/feed-posts/{feedPost}', [FeedPostController::class, 'updateForGroup']);
        Route::delete('/{group}/feed-posts/{feedPost}', [FeedPostController::class, 'destroyForGroup']);
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

        Route::post('/{event}/tickets/validate', [TicketValidationController::class, 'store']);

        Route::post('/{event}/accepted-support-types', [AcceptedSupportTypeController::class, 'storeForEvent']);
        Route::delete('/{event}/accepted-support-types/{acceptedSupportType}', [AcceptedSupportTypeController::class, 'destroyForEvent']);

        Route::get('/{event}/sponsorships/pending', [SponsorshipController::class, 'pendingForEvent']);
        Route::post('/{event}/sponsorships', [SponsorshipController::class, 'storeForEvent']);

        Route::post('/{event}/follow', [FollowController::class, 'followEvent']);
        Route::delete('/{event}/follow', [FollowController::class, 'unfollowEvent']);

        Route::post('/{event}/favorite', [FavoriteController::class, 'store']);
        Route::delete('/{event}/favorite', [FavoriteController::class, 'destroy']);
    });

    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/{ticket}', [TicketController::class, 'show']);
    });

    Route::prefix('sponsorships')->group(function () {
        Route::get('/', [SponsorshipController::class, 'index']);
        Route::patch('/{sponsorship}', [SponsorshipController::class, 'update']);
        Route::delete('/{sponsorship}', [SponsorshipController::class, 'destroy']);
    });

    Route::prefix('opportunities')->group(function () {
        Route::post('/', [OpportunityController::class, 'store']);
        Route::patch('/{opportunity}', [OpportunityController::class, 'update']);
        Route::delete('/{opportunity}', [OpportunityController::class, 'destroy']);

        Route::get('/{opportunity}/applications', [OpportunityApplicationController::class, 'index']);
        Route::post('/{opportunity}/applications', [OpportunityApplicationController::class, 'store']);
        Route::patch('/{opportunity}/applications/{application}', [OpportunityApplicationController::class, 'update']);
        Route::delete('/{opportunity}/applications/{application}', [OpportunityApplicationController::class, 'destroy']);
    });

    Route::prefix('opportunity-applications')->group(function () {
        Route::get('/', [OpportunityApplicationController::class, 'mine']);
    });

    Route::get('/following', [FollowController::class, 'following']);
    Route::get('/favorites', [FavoriteController::class, 'index']);

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
