<?php

namespace Tests\Feature\Models;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_persisted_venue(): void
    {
        $venue = Venue::factory()->create([
            'ven_cidade' => 'São Paulo',
            'ven_estado' => 'SP',
        ]);

        $this->assertDatabaseHas('venues', [
            'id' => $venue->id,
            'ven_cidade' => 'São Paulo',
            'ven_estado' => 'SP',
            'ven_active' => true,
        ]);
    }
}
