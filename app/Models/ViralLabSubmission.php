<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViralLabSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'post_text',
        'platform',
        'likes',
        'comments',
        'shares',
        'word_count',
        'detected_drivers',
        'new_driver_flag',
        'new_driver_name',
        'ai_analysis',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'detected_drivers' => 'array',
            'ai_analysis' => 'array',
            'new_driver_flag' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
