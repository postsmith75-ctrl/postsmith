<?php

namespace App\Services\Postsmith;

use App\Models\User;
use App\Models\ViralLabSubmission;
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

    public function viralLabStatus(User $user): array
    {
        $this->resetMonthlyIfNeeded($user);

        $limit = (int) $this->tierConfig($user)['viral_lab'];
        $used = ViralLabSubmission::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $user->generations_reset_at ?: now()->subMonth())
            ->count();

        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => $limit < 0 ? -1 : max(0, $limit - $used),
            'blocked' => $limit >= 0 && $used >= $limit && ! $this->isOwner($user),
        ];
    }

    public function canUseViralLab(User $user): bool
    {
        return ! $this->viralLabStatus($user)['blocked'];
    }

    public function historyDays(?User $user): int
    {
        return (int) $this->tierConfig($user)['history_days'];
    }

    public function starLimit(User $user): int
    {
        return $user->hasFullAccess() ? -1 : 5;
    }

    public function canStarAnotherPost(User $user): bool
    {
        $limit = $this->starLimit($user);

        return $limit < 0 || $user->posts()->where('is_starred', true)->count() < $limit || $this->isOwner($user);
    }

    public function canUseRss(User $user): bool
    {
        return $user->isStarter() || $user->isPro() || $this->isOwner($user);
    }

    public function canExportCsv(User $user): bool
    {
        return $user->isPro() || $this->isOwner($user);
    }

    public function tierConfig(?User $user): array
    {
        $tier = $user?->tier ?: 'free';
        if ($user && ! $this->isOwner($user) && in_array($tier, ['starter', 'pro'], true) && $user->pro_expires_at && $user->pro_expires_at->isPast()) {
            $tier = 'free';
        }

        return config("postsmith.tiers.{$tier}", config('postsmith.tiers.free'));
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
