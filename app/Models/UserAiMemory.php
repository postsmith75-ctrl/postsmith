<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAiMemory extends Model
{
    protected $table = 'user_memories';

    protected $fillable = [
        'user_id',
        'type',
        'source',
        'title',
        'content',
        'metadata',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
