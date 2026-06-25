<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'tx_ref',
        'provider_transaction_id',
        'status',
        'purpose',
        'tier',
        'plan',
        'amount',
        'currency',
        'auto_renew_requested',
        'payment_method',
        'card_brand',
        'card_last_four',
        'card_expiry',
        'provider_customer_id',
        'paid_at',
        'raw_payload',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'auto_renew_requested' => 'boolean',
            'paid_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }
}
