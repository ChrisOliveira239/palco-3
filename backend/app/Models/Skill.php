<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ski_nome',
        'ski_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ski_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<ArtistProfile, $this>
     */
    public function artistProfiles(): BelongsToMany
    {
        return $this->belongsToMany(ArtistProfile::class, 'artist_profile_skill');
    }
}
