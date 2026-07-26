<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupMember extends Pivot
{
    /**
     * The table's `group_members.id` is a real auto-incrementing primary key,
     * unlike a default Eloquent pivot.
     */
    public $incrementing = true;

    /**
     * The table only has `created_at` (no `updated_at`). Setting UPDATED_AT to
     * null alone isn't enough — Eloquent's model-level save() still tries to
     * write both columns during attach() via a custom pivot class. Disabling
     * timestamps entirely avoids that; `created_at` is still populated via
     * `withPivot('grm_papel', 'created_at')` on the relation (or by the
     * column's own `useCurrent()` DB default as a fallback).
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group_id',
        'user_id',
        'grm_papel',
    ];
}
