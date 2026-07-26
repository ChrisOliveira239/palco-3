<?php

namespace App\Models;

use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ven_nome',
        'ven_endereco',
        'ven_cidade',
        'ven_estado',
        'ven_latitude',
        'ven_longitude',
        'ven_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ven_latitude' => 'decimal:7',
            'ven_longitude' => 'decimal:7',
            'ven_active' => 'boolean',
        ];
    }
}
