<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGeneratorPreference extends Model
{
    protected $fillable = [
        'user_id',
        'last_platform',
        'last_goal',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
