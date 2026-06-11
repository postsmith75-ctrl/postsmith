<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenerationHistory extends Model
{
    protected $table = 'generation_history';

    protected $fillable = [
        'user_id',
        'mode',
        'input_text',
        'platform',
        'length',
        'generated_json',
    ];

    protected function casts(): array
    {
        return [
            'generated_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
