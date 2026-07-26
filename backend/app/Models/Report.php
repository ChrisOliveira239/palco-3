<?php

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'denunciante_id',
        'alvo_type',
        'alvo_id',
        'rep_motivo',
        'rep_status',
        'rep_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rep_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function denunciante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'denunciante_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function alvo(): MorphTo
    {
        return $this->morphTo();
    }
}
