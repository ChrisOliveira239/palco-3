<?php

namespace App\Models;

use App\Enums\SponsorshipStatus;
use App\Enums\TipoApoio;
use Database\Factories\SponsorshipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Sponsorship extends Model
{
    /** @use HasFactory<SponsorshipFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sponsor_type',
        'sponsor_id',
        'alvo_type',
        'alvo_id',
        'spo_tipo_apoio',
        'spo_valor',
        'spo_descricao',
        'spo_status',
        'spo_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'spo_tipo_apoio' => TipoApoio::class,
            'spo_valor' => 'decimal:2',
            'spo_status' => SponsorshipStatus::class,
            'spo_active' => 'boolean',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function sponsor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function alvo(): MorphTo
    {
        return $this->morphTo();
    }
}
