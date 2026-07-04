<?php

namespace App\Data;

/**
 * Represents a user's strategy selection.
 *
 * Holds both the key (lowercase identifier) and the label (display name)
 * for platform, goal, and length selections.
 */
class StrategySelection
{
    public function __construct(
        public string $platform_key,
        public string $platform_label,
        public string $goal_key,
        public string $goal_label,
        public string $length_key,
        public string $length_label,
    ) {}

    /**
     * Create a StrategySelection from raw user inputs.
     *
     * @param string $platform The platform display name
     * @param string $goal The goal display name
     * @param string $length The length key (short, medium, long)
     * @return self
     */
    public static function from(string $platform, string $goal, string $length): self
    {
        return new self(
            platform_key: strtolower($platform),
            platform_label: $platform,
            goal_key: $goal,
            goal_label: $goal,
            length_key: strtolower($length),
            length_label: $length,
        );
    }

    /**
     * Convert to array for serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'platform_key' => $this->platform_key,
            'platform_label' => $this->platform_label,
            'goal_key' => $this->goal_key,
            'goal_label' => $this->goal_label,
            'length_key' => $this->length_key,
            'length_label' => $this->length_label,
        ];
    }
}
