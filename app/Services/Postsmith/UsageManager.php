<?php

namespace App\Services\Postsmith;

use App\Models\User;
use Illuminate\Support\Carbon;

class UsageManager
{
    public function status(?User $user): array
    {
        if (! $user) {
            return ['used' => 0, 'limit' => 3, 'remaining' => 3, 'blocked' => false, 'label' => 'Guest'];
        }

        $this->resetMonthlyIfNeeded($user);

        $tier = $user->tier ?: 'free';
        $config = config("postsmith.tiers.{$tier}", config('postsmith.tiers.free'));
        $limit = (int) $config['generations'];
        $used = (int) $user->generations_used;

        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => max(0, $limit - $used),
            'blocked' => $used >= $limit && ! $this->isOwner($user),
            'label' => $config['label'],
        ];
    }

    public function canGenerate(?User $user): bool
    {
        return ! $this->status($user)['blocked'];
    }

    public function increment(?User $user): void
    {
        if (! $user || $this->isOwner($user)) {
            return;
        }

        $this->resetMonthlyIfNeeded($user);

        $user->forceFill([
            'generations_used' => $user->generations_used + 1,
            'last_generation_at' => now(),
        ])->save();
    }

    private function resetMonthlyIfNeeded(User $user): void
    {
        if (! $user->generations_reset_at || $user->generations_reset_at->lt(Carbon::now()->subMonth())) {
            $user->forceFill([
                'generations_used' => 0,
                'generations_reset_at' => now(),
            ])->save();
        }
    }

    private function isOwner(User $user): bool
    {
        return $user->isAdmin();
    }
}
