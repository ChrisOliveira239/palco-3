<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'eve_titulo',
        'eve_descricao',
        'category_id',
        'organizador_type',
        'organizador_id',
        'eve_status',
        'eve_gratuito',
        'eve_cartaz_url',
        'eve_links_externos',
        'aprovado_por_id',
        'eve_aprovado_em',
        'eve_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'eve_gratuito' => 'boolean',
            'eve_links_externos' => 'array',
            'eve_aprovado_em' => 'datetime',
            'eve_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function organizador(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por_id');
    }

    /**
     * @return HasMany<EventSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class);
    }

    /**
     * @return HasMany<EventMedia, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(EventMedia::class);
    }

    /**
     * @return BelongsToMany<ArtistProfile, $this>
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(ArtistProfile::class, 'event_artist')
            ->using(EventArtist::class)
            ->withPivot('eva_status');
    }

    /**
     * @return BelongsToMany<Group, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'event_group')
            ->using(EventGroup::class)
            ->withPivot('evg_status');
    }

    /**
     * @return MorphMany<Sponsorship, $this>
     */
    public function sponsorships(): MorphMany
    {
        return $this->morphMany(Sponsorship::class, 'alvo');
    }

    /**
     * @return MorphMany<AcceptedSupportType, $this>
     */
    public function acceptedSupportTypes(): MorphMany
    {
        return $this->morphMany(AcceptedSupportType::class, 'alvo');
    }

    /**
     * @return MorphMany<Follow, $this>
     */
    public function followers(): MorphMany
    {
        return $this->morphMany(Follow::class, 'seguivel');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    /**
     * @return HasMany<EventReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(EventReview::class);
    }

    /**
     * @return MorphMany<Report, $this>
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'alvo');
    }
}
