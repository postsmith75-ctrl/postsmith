<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscoveredDriver extends Model
{
    protected $fillable = [
        'driver_name',
        'description',
        'psychology',
        'submissions_count',
        'avg_confidence',
        'status',
        'discovered_by_user_id',
        'promoted_at',
        'new_until',
        'notification_sent',
    ];

    protected function casts(): array
    {
        return [
            'avg_confidence' => 'float',
            'promoted_at' => 'datetime',
            'new_until' => 'datetime',
            'notification_sent' => 'boolean',
        ];
    }
}
