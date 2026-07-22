<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'use_name' => 'Test User',
            'email' => 'test@example.com',
            'use_is_admin' => true,
        ]);

        $this->call([
            UsersSeeder::class,
            CategoriesSeeder::class,
            VenuesSeeder::class,
            SkillsSeeder::class,
            ArtistProfilesSeeder::class,
            GroupsSeeder::class,
            GroupMembersSeeder::class,
            ArtistProfileSkillSeeder::class,
            EventsSeeder::class,
            EventSessionsSeeder::class,
            EventArtistSeeder::class,
            EventGroupSeeder::class,
            EventMediaSeeder::class,
            TicketTypesSeeder::class,
            TicketsSeeder::class,
            SponsorshipsSeeder::class,
            AcceptedSupportTypesSeeder::class,
            FollowsSeeder::class,
            FavoritesSeeder::class,
            FeedPostsSeeder::class,
            EventReviewsSeeder::class,
            OpportunitiesSeeder::class,
            OpportunityApplicationsSeeder::class,
            NotificationsSeeder::class,
            ReportsSeeder::class,
        ]);
    }
}
