<?php

namespace App\Services\Postsmith;

use App\Models\User;
use App\Models\UserGeneratorPreference;

class GeneratorPreferences
{
    /**
     * @return array{last_platform: string|null, last_goal: string|null, last_length: string|null}
     */
    public function forUser(?User $user): array
    {
        $defaultPlatform = config('postsmith.generator.defaults.platform');
        $defaultGoal = config('postsmith.generator.defaults.goal');
        $defaultLength = config('postsmith.generator.defaults.length');

        if (! $user) {
            return [
                'last_platform' => $defaultPlatform,
                'last_goal' => $defaultGoal,
                'last_length' => $defaultLength,
            ];
        }

        $preferences = $user->generatorPreference;

        return [
            'last_platform' => $this->configuredValue($preferences?->last_platform, 'platforms', $defaultPlatform),
            'last_goal' => $this->configuredValue($preferences?->last_goal, 'goals', $defaultGoal),
            'last_length' => $this->configuredValue($preferences?->last_length, 'lengths', $defaultLength),
        ];
    }

    /**
     * @param array{platform?: string|null, goal?: string|null, length?: string|null} $settings
     */
    public function remember(User $user, array $settings): UserGeneratorPreference
    {
        $payload = [];

        if (array_key_exists('platform', $settings)) {
            $payload['last_platform'] = $settings['platform'];
        }

        if (array_key_exists('goal', $settings)) {
            $payload['last_goal'] = $settings['goal'];
        }

        if (array_key_exists('length', $settings)) {
            $payload['last_length'] = $settings['length'];
        }

        return $user->generatorPreference()->updateOrCreate([], $payload);
    }

    private function configuredValue(?string $value, string $key, string $default): string
    {
        if ($value && in_array($value, config("postsmith.generator.{$key}", []), true)) {
            return $value;
        }

        return $default;
    }
}
