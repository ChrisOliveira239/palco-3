<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventArtist extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'artist_profile_id',
        'eva_status',
    ];
}
