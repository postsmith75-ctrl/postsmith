<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingExample extends Model
{
    protected $fillable = [
        'driver_name',
        'raw_thought',
        'transformed_post',
        'platform',
        'source',
        'engagement_score',
    ];
}
