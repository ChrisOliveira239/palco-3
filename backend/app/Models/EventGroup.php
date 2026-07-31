<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventGroup extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'group_id',
        'evg_status',
    ];
}
