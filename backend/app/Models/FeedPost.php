<?php

namespace App\Models;

use App\Enums\FeedPostType;
use Database\Factories\FeedPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedPost extends Model
{
    /** @use HasFactory<FeedPostFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'autor_type',
        'autor_id',
        'fee_tipo',
        'fee_conteudo',
        'fee_midia_url',
        'fee_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fee_tipo' => FeedPostType::class,
            'fee_active' => 'boolean',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function autor(): MorphTo
    {
        return $this->morphTo();
    }
}
