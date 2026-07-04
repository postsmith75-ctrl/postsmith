<?php

namespace App\Services\Postsmith;

use App\Data\StrategyContext;
use App\Data\StrategySelection;

class StrategyRules
{
    /**
     * Resolve a complete Strategy Context from platform, goal, and length selections.
     *
     * @param string $platform The selected platform (LinkedIn, Facebook, Instagram, X).
     * @param string $goal The selected goal (Get Engagement, Generate Leads, etc.).
     * @param string $length The selected length (short, medium, long).
     *
     * @return \App\Data\StrategyContext Structured strategy context with selection and rules.
     */
    public function resolve(string $platform, string $goal, string $length): StrategyContext
    {
        $platformData = $this->platformRulesWithMetadata($platform);
        $goalData = $this->goalRulesWithMetadata($goal);
        $lengthData = $this->lengthRulesWithMetadata($length);

        $strategy = new StrategySelection(
            platform_key: $platformData['key'],
            platform_label: $platformData['label'],
            goal_key: $goalData['key'],
            goal_label: $goalData['label'],
            length_key: $lengthData['key'],
            length_label: $lengthData['label'],
        );

        return new StrategyContext(
            strategy: $strategy,
            platformRules: $platformData['rules'],
            goalRules: $goalData['rules'],
            lengthRules: $lengthData['rules'],
        );
    }

    /**
     * Get platform-specific writing rules with metadata.
     *
     * @param string $platform The platform name.
     *
     * @return array{key: string, label: string, rules: array} Platform metadata and rules.
     */
    private function platformRulesWithMetadata(string $platform): array
    {
        $platformKey = strtolower($platform);
        $platformRules = config('postsmith.platform_rules', []);

        if (array_key_exists($platformKey, $platformRules)) {
            return [
                'key' => $platformKey,
                'label' => $platform,
                'rules' => $platformRules[$platformKey],
            ];
        }

        // Fallback to defaults
        $defaultPlatform = config('postsmith.generator.defaults.platform', 'LinkedIn');
        $defaultKey = strtolower($defaultPlatform);

        return [
            'key' => $defaultKey,
            'label' => $defaultPlatform,
            'rules' => $platformRules[$defaultKey] ?? [],
        ];
    }

    /**
     * Get goal-specific content rules with metadata.
     *
     * @param string $goal The goal name.
     *
     * @return array{key: string, label: string, rules: array} Goal metadata and rules.
     */
    private function goalRulesWithMetadata(string $goal): array
    {
        $goalRules = config('postsmith.goal_rules', []);

        if (array_key_exists($goal, $goalRules)) {
            return [
                'key' => $goal,
                'label' => $goal,
                'rules' => $goalRules[$goal],
            ];
        }

        // Fallback to defaults
        $defaultGoal = config('postsmith.generator.defaults.goal', 'Get Engagement');

        return [
            'key' => $defaultGoal,
            'label' => $defaultGoal,
            'rules' => $goalRules[$defaultGoal] ?? [],
        ];
    }

    /**
     * Get length-specific writing guidance with metadata.
     *
     * @param string $length The length (short, medium, long).
     *
     * @return array{key: string, label: string, rules: array} Length metadata and rules.
     */
    private function lengthRulesWithMetadata(string $length): array
    {
        $lengthKey = strtolower($length);
        $lengthRules = config('postsmith.length_rules', []);

        if (array_key_exists($lengthKey, $lengthRules)) {
            return [
                'key' => $lengthKey,
                'label' => $lengthKey,
                'rules' => $lengthRules[$lengthKey],
            ];
        }

        // Fallback to defaults
        $defaultLength = strtolower(config('postsmith.generator.defaults.length', 'medium'));

        return [
            'key' => $defaultLength,
            'label' => $defaultLength,
            'rules' => $lengthRules[$defaultLength] ?? [],
        ];
    }
}
