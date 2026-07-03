<?php

namespace App\Services\Postsmith;

use App\Models\User;
use App\Models\UserAiMemory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class AiMemory
{
    private const TYPE_PROFILE = 'profile';
    private const SOURCE_ONBOARDING = 'onboarding';
    private const PROMPT_MEMORY_LIMIT = 8;

    /**
     * @param array{use_case: string, brand_context?: string|null} $data
     */
    public function saveOnboardingMemory(User $user, array $data): UserAiMemory
    {
        $useCase = trim($data['use_case']);
        $about = trim((string) ($data['brand_context'] ?? ''));

        $content = $about !== ''
            ? $about
            : "The user is using PostSmith for {$useCase}.";

        return $user->aiMemories()->updateOrCreate(
            [
                'type' => self::TYPE_PROFILE,
                'source' => self::SOURCE_ONBOARDING,
            ],
            [
                'title' => 'Profile',
                'content' => $content,
                'metadata' => [
                    'use_case' => $useCase,
                    'about_provided' => $about !== '',
                ],
                'active' => true,
            ],
        );
    }

    public function buildPromptContext(?User $user): string
    {
        if (! $user) {
            return '';
        }

        $memories = $this->retrieveRelevantMemories($user);

        if ($memories->isEmpty()) {
            return '';
        }

        $lines = $memories->map(function (UserAiMemory $memory): string {
            $useCase = data_get($memory->metadata, 'use_case');
            $prefix = $memory->title ?: Str::headline($memory->type);
            $suffix = $useCase ? " Use case: {$useCase}." : '';

            return "- {$prefix}: {$memory->content}{$suffix}";
        })->implode("\n");

        return <<<MEMORY
User AI Memory:
{$lines}

Use this memory as helpful long-term context. The user's current prompt is more specific and should override memory when there is a conflict.
MEMORY;
    }

    public function promptContext(?User $user): string
    {
        return $this->buildPromptContext($user);
    }

    /**
     * V2 intentionally returns all active memories, newest first.
     *
     * This is the retrieval boundary for future AI learning. Later versions can
     * replace the ordering/filtering here with ranking by relevance, importance,
     * recency, AI confidence, memory type, or event-derived signals without
     * changing ContentGenerator or the prompt formatting code.
     *
     * Future memory sources may include generated content, edited AI output,
     * published posts, tone changes, platform habits, connected accounts,
     * successful posts, user corrections, and preferred writing styles.
     *
     * @return Collection<int, UserAiMemory>
     */
    protected function retrieveRelevantMemories(User $user): Collection
    {
        return $user->aiMemories()
            ->where('active', true)
            ->latest()
            ->limit(self::PROMPT_MEMORY_LIMIT)
            ->get();
    }
}
