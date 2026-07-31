<?php

namespace App\Models;

use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Group extends Model
{
    /** @use HasFactory<GroupFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gro_nome',
        'gro_descricao',
        'gro_avatar_url',
        'gro_capa_url',
        'gro_cnpj',
        'gro_telefone',
        'gro_email',
        'gro_site',
        'user_id',
        'gro_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gro_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->using(GroupMember::class)
            ->withPivot('grm_papel');
    }

    /**
     * @return BelongsToMany<Event, $this>
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_group')
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
