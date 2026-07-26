<?php

namespace App\Models;

use Database\Factories\AcceptedSupportTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AcceptedSupportType extends Model
{
    /** @use HasFactory<AcceptedSupportTypeFactory> */
    use HasFactory;

    /**
     * The table only has `created_at` (no `updated_at`), filled by the
     * column's own `useCurrent()` DB default — same case as `GroupMember`.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'alvo_type',
        'alvo_id',
        'ast_tipo_apoio',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function alvo(): MorphTo
    {
        return $this->morphTo();
    }
}
