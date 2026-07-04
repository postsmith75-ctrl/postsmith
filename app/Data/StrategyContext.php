<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\StrategySelection;

/**
 * Complete strategy context with resolved rules.
 *
 * Contains the user's strategy selection and all applicable writing rules
 * for platform, goal, and length.
 */
class StrategyContext
{
    public function __construct(
        public StrategySelection $strategy,
        public array $platformRules,
        public array $goalRules,
        public array $lengthRules,
    ) {
    }

    /**
     * Convert to array for serialization or debugging.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'strategy' => $this->strategy->toArray(),
            'platformRules' => $this->platformRules,
            'goalRules' => $this->goalRules,
            'lengthRules' => $this->lengthRules,
        ];
    }

    /**
     * Get platform rules as legacy array format (for backward compatibility).
     *
     * @return array
     */
    public function platformRulesLegacy(): array
    {
        return array_merge(
            ['platform' => $this->strategy->platform_label],
            $this->platformRules,
        );
    }

    /**
     * Get goal rules as legacy array format (for backward compatibility).
     *
     * @return array
     */
    public function goalRulesLegacy(): array
    {
        return array_merge(
            ['goal' => $this->strategy->goal_label],
            $this->goalRules,
        );
    }

    /**
     * Get length rules as legacy array format (for backward compatibility).
     *
     * @return array
     */
    public function lengthRulesLegacy(): array
    {
        return array_merge(
            ['length' => $this->strategy->length_key],
            $this->lengthRules,
        );
    }

    /**
     * Get all rules combined in legacy format.
     *
     * @return array
     */
    public function toLegacyArray(): array
    {
        return [
            'platform' => $this->platformRulesLegacy(),
            'goal' => $this->goalRulesLegacy(),
            'length' => $this->lengthRulesLegacy(),
        ];
    }
}
