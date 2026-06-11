<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'length',
        'driver',
        'post_text',
        'likes',
        'comments',
        'shares',
        'raw_thought',
        'zernio_post_id',
        'last_synced_at',
        'media_url',
        'media_type',
        'is_starred',
        'starred_at',
    ];

    protected function casts(): array
    {
        return [
            'likes' => 'integer',
            'comments' => 'integer',
            'shares' => 'integer',
            'is_starred' => 'boolean',
            'last_synced_at' => 'datetime',
            'starred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function engagementScore(): int
    {
        return $this->likes + ($this->comments * 2) + ($this->shares * 3);
    }
}
