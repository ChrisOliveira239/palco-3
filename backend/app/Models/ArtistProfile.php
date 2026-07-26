<?php

namespace App\Models;

use Database\Factories\ArtistProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ArtistProfile extends Model
{
    /** @use HasFactory<ArtistProfileFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'art_nome_artistico',
        'art_bio',
        'art_capa_url',
        'art_verificado',
        'art_drt',
        'art_telefone',
        'art_email',
        'art_site',
        'art_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'art_verificado' => 'boolean',
            'art_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'artist_profile_skill');
    }

    /**
     * @return BelongsToMany<Event, $this>
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_artist');
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
     * @return MorphMany<FeedPost, $this>
     */
    public function feedPosts(): MorphMany
    {
        return $this->morphMany(FeedPost::class, 'autor');
    }

    /**
     * @return MorphMany<Opportunity, $this>
     */
    public function opportunities(): MorphMany
    {
        return $this->morphMany(Opportunity::class, 'criador');
    }

    /**
     * @return MorphMany<Report, $this>
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'alvo');
    }
}
