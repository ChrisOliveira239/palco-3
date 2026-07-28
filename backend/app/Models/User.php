<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'use_name',
        'email',
        'password',
        'use_avatar_url',
        'use_bio',
        'use_city',
        'use_state',
        'use_latitude',
        'use_longitude',
        'use_is_admin',
        'use_tipo_conta',
        'use_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->use_is_admin;
    }

    public function isArtist(): bool
    {
        return $this->artistProfile()->exists();
    }

    public function isGroupOwner(Group $group): bool
    {
        return $this->id === $group->user_id;
    }

    public function isGroupAdmin(Group $group): bool
    {
        return $group->members()
            ->wherePivot('user_id', $this->id)
            ->wherePivot('grm_papel', 'ADMIN')
            ->exists();
    }

    /**
     * @return HasOne<ArtistProfile, $this>
     */
    public function artistProfile(): HasOne
    {
        return $this->hasOne(ArtistProfile::class);
    }

    /**
     * @return HasMany<Group, $this>
     */
    public function ownedGroups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * @return BelongsToMany<Group, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->using(GroupMember::class)
            ->withPivot('grm_papel');
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * @return MorphMany<Sponsorship, $this>
     */
    public function sponsorships(): MorphMany
    {
        return $this->morphMany(Sponsorship::class, 'sponsor');
    }

    /**
     * @return HasMany<Follow, $this>
     */
    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }

    /**
     * @return BelongsToMany<Event, $this>
     */
    public function favoriteEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'favorites');
    }

    /**
     * @return HasMany<EventReview, $this>
     */
    public function eventReviews(): HasMany
    {
        return $this->hasMany(EventReview::class);
    }

    /**
     * @return HasMany<OpportunityApplication, $this>
     */
    public function opportunityApplications(): HasMany
    {
        return $this->hasMany(OpportunityApplication::class);
    }

    /**
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'denunciante_id');
    }
}
