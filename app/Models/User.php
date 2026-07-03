<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'tier',
        'role',
        'google_id',
        'email_verified',
        'onboarding_completed_at',
        'generations_used',
        'generations_reset_at',
        'pro_expires_at',
        'billing_auto_renew',
        'billing_plan',
        'payment_provider_customer_id',
        'billing_card_brand',
        'billing_card_last_four',
        'billing_card_expires',
        'billing_card_updated_at',
        'viral_reset_at',
        'zernio_api_key',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function generationHistory(): HasMany
    {
        return $this->hasMany(GenerationHistory::class);
    }

    public function aiMemories(): HasMany
    {
        return $this->hasMany(UserAiMemory::class, 'user_id');
    }

    public function memories(): HasMany
    {
        return $this->aiMemories();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPro(): bool
    {
        return $this->tier === 'pro' && (! $this->pro_expires_at || $this->pro_expires_at->isFuture());
    }

    public function isStarter(): bool
    {
        return $this->tier === 'starter' && (! $this->pro_expires_at || $this->pro_expires_at->isFuture());
    }

    public function hasFullAccess(): bool
    {
        return $this->isPro() || $this->isStarter() || $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        $emails = collect(array_merge(
            explode(',', (string) config('postsmith.admin_email')),
            config('postsmith.admin_emails', []),
        ))
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter()
            ->all();

        return in_array(strtolower($this->email), $emails, true)
            || in_array($this->role, ['admin', 'owner'], true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verified' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'verification_sent_at' => 'datetime',
            'reset_expires_at' => 'datetime',
            'generations_reset_at' => 'datetime',
            'pro_expires_at' => 'datetime',
            'billing_auto_renew' => 'boolean',
            'billing_card_updated_at' => 'datetime',
            'viral_reset_at' => 'datetime',
            'username_changed_at' => 'datetime',
            'last_generation_at' => 'datetime',
            'last_streak_date' => 'date',
            'zernio_connected_at' => 'datetime',
            'last_weekly_digest' => 'date',
            'password' => 'hashed',
        ];
    }
}
