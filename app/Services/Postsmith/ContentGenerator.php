<?php

namespace App\Services\Postsmith;

use App\Models\User;
use App\Models\TrainingExample;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ContentGenerator
{
    public function __construct(private readonly AiMemory $memory)
    {
    }

    public function fromThought(string $thought, string $platform, string $length, array $drivers = [], ?User $user = null): array
    {
        $drivers = $this->normalizeDrivers($drivers);

        if ($this->hasAiConfig()) {
            $ai = $this->callAi(
                'You are PostSmith, a senior social content strategist. Return strict JSON with a posts array. Each item must have driver, text, and why_it_works.',
                $this->generationPrompt($thought, $platform, $length, $drivers, $user),
                1800
            );

            if (is_array($ai) && isset($ai['posts']) && is_array($ai['posts'])) {
                return ['posts' => $this->sortByDrivers($ai['posts'], $drivers), 'source' => 'ai'];
            }
        }

        return ['posts' => $this->fallbackGenerate($thought, $platform, $length, $drivers), 'source' => 'fallback'];
    }

    public function rewrite(string $draft, string $platform, string $length, array $drivers = [], ?User $user = null): array
    {
        $drivers = $this->normalizeDrivers($drivers);

        if ($this->hasAiConfig()) {
            $ai = $this->callAi(
                'You rewrite social posts while preserving the writer emotion and message. Return strict JSON with a rewrites array. Each item must have driver, text, and what_changed.',
                $this->rewritePrompt($draft, $platform, $length, $drivers, $user),
                1800
            );

            if (is_array($ai) && isset($ai['rewrites']) && is_array($ai['rewrites'])) {
                return ['rewrites' => $this->sortByDrivers($ai['rewrites'], $drivers), 'source' => 'ai'];
            }
        }

        return ['rewrites' => $this->fallbackRewrite($draft, $platform, $length, $drivers), 'source' => 'fallback'];
    }

    public function analyzeViralPost(string $postText, string $platform): array
    {
        if ($this->hasAiConfig()) {
            $ai = $this->callAi(
                'You analyze why social posts perform. Return strict JSON with analysis: dominant_driver, detected_drivers, why_it_works, confidence, new_driver_detected, new_driver_name, new_driver_description.',
                "Platform: {$platform}\n\nPost:\n{$postText}",
                1400
            );

            if (is_array($ai) && isset($ai['analysis'])) {
                return ['analysis' => $ai['analysis'], 'source' => 'ai'];
            }
        }

        return [
            'analysis' => [
                'dominant_driver' => 'Story',
                'detected_drivers' => ['Story', 'Real Talk', 'Open Up'],
                'why_it_works' => 'The post appears to use concrete experience, emotional honesty, and a clear lesson, which are reliable engagement patterns.',
                'confidence' => 72,
                'new_driver_detected' => false,
                'new_driver_name' => null,
                'new_driver_description' => null,
            ],
            'source' => 'fallback',
        ];
    }

    private function generationPrompt(string $thought, string $platform, string $length, array $drivers, ?User $user): string
    {
        $examples = $this->trainingBlock($drivers, $platform);
        $driverList = implode(', ', $drivers);
        $memory = $this->memoryBlock($user);

        return <<<PROMPT
Platform: {$platform}
Length: {$length}
Drivers to use: {$driverList}
{$memory}
Raw thought: {$thought}
{$examples}

Create one post per driver. Preserve the user's emotion and meaning, but improve the hook, structure, clarity, and engagement.
PROMPT;
    }

    private function rewritePrompt(string $draft, string $platform, string $length, array $drivers, ?User $user): string
    {
        $driverList = implode(', ', $drivers);
        $memory = $this->memoryBlock($user);

        return <<<PROMPT
Platform: {$platform}
Length: {$length}
Drivers to use: {$driverList}
{$memory}
Draft: {$draft}

Rewrite this post once per driver. Preserve the original point. Improve opening line, rhythm, specificity, and ending.
PROMPT;
    }

    private function memoryBlock(?User $user): string
    {
        $memory = $this->memory->buildPromptContext($user);

        return $memory === '' ? '' : "\n{$memory}\n";
    }

    private function trainingBlock(array $drivers, string $platform): string
    {
        $examples = TrainingExample::query()
            ->whereIn('driver_name', $drivers)
            ->where(function ($query) use ($platform) {
                $query->where('platform', $platform)->orWhere('platform', 'general');
            })
            ->orderByDesc('engagement_score')
            ->limit(4)
            ->get();

        if ($examples->isEmpty()) {
            return '';
        }

        return $examples
            ->map(fn (TrainingExample $example, int $i) => sprintf(
                "\nExample %d - Driver: %s\nRaw thought: \"%s\"\nTransformed post: \"%s\"",
                $i + 1,
                $example->driver_name,
                $example->raw_thought,
                $example->transformed_post
            ))
            ->implode("\n");
    }

    private function hasAiConfig(): bool
    {
        return filled(config('postsmith.ai.api_key'));
    }

    private function callAi(string $systemPrompt, string $userPrompt, int $maxTokens): ?array
    {
        $provider = strtolower(config('postsmith.ai.provider', 'groq'));
        $endpoint = Arr::get(config('postsmith.ai.endpoints'), $provider);
        $key = config('postsmith.ai.api_key');

        if (! $endpoint || ! $key) {
            return null;
        }

        try {
            if ($provider === 'gemini') {
                $response = Http::timeout(30)->post($endpoint . '?key=' . $key, [
                    'contents' => [[
                        'parts' => [[
                            'text' => $systemPrompt . "\n\n" . $userPrompt,
                        ]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.8,
                        'maxOutputTokens' => $maxTokens,
                    ],
                ]);

                $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
            } else {
                $response = Http::timeout(30)
                    ->withToken($key)
                    ->post($endpoint, [
                        'model' => $this->modelFor($provider),
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $userPrompt],
                        ],
                        'temperature' => 0.8,
                        'max_tokens' => $maxTokens,
                        'response_format' => ['type' => 'json_object'],
                    ]);

                $text = data_get($response->json(), 'choices.0.message.content');
            }

            if (! $response->successful() || ! $text) {
                return null;
            }

            $json = json_decode($this->extractJson($text), true);

            return is_array($json) ? $json : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function modelFor(string $provider): string
    {
        return match ($provider) {
            'openai' => 'gpt-4o-mini',
            'deepseek' => 'deepseek-chat',
            'groq' => 'llama-3.3-70b-versatile',
            default => 'gpt-4o-mini',
        };
    }

    private function extractJson(string $text): string
    {
        $text = trim($text);

        if (Str::startsWith($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);
        }

        return trim($text);
    }

    private function fallbackGenerate(string $thought, string $platform, string $length, array $drivers): array
    {
        return collect($drivers)->map(function (string $driver) use ($thought, $platform, $length) {
            $text = match ($driver) {
                'Real Talk' => "Real talk: {$thought}\n\nMost people overcomplicate this. Say the honest thing, make it useful, and give people a reason to respond.",
                'Open Up' => "I used to hold back from saying this:\n\n{$thought}\n\nBut the more I think about it, the more I realize someone else probably needs to hear it too.",
                'Teaser' => "There is one part of this people usually miss:\n\n{$thought}\n\nOnce you notice it, the whole thing starts to make more sense.",
                'Free Gift' => "Save this if you are working through something similar:\n\n{$thought}\n\nThe simple version: name the problem, tell the truth, then give people one next step.",
                'Story' => "A quick story:\n\n{$thought}\n\nThe lesson was not obvious at first, but it changed how I think about creating, sharing, and showing up.",
                default => "{$thought}\n\nThat is the point. Simple, direct, and worth testing on {$platform}.",
            };

            return [
                'driver' => $driver,
                'text' => $this->fitLength($text, $length),
                'why_it_works' => "{$driver} gives the post a clearer emotional angle and stronger reason to keep reading.",
            ];
        })->all();
    }

    private function fallbackRewrite(string $draft, string $platform, string $length, array $drivers): array
    {
        return collect($drivers)->map(function (string $driver) use ($draft, $length) {
            $text = match ($driver) {
                'Real Talk' => "Real talk:\n\n{$draft}\n\nThat is the honest version. And honesty is usually what makes people stop scrolling.",
                'Open Up' => "I want to be honest about this:\n\n{$draft}\n\nIt is not polished, but it is true. That is why it matters.",
                'Teaser' => "The part nobody talks about:\n\n{$draft}\n\nAnd that is exactly where the lesson is.",
                'Free Gift' => "A useful reminder:\n\n{$draft}\n\nKeep this in mind the next time you are trying to turn a raw thought into something people feel.",
                'Story' => "Here is what happened:\n\n{$draft}\n\nThe takeaway is simple, but it took living through it to understand.",
                default => $draft,
            };

            return [
                'driver' => $driver,
                'text' => $this->fitLength($text, $length),
                'what_changed' => "Reframed with the {$driver} pattern for a sharper hook and clearer payoff.",
            ];
        })->all();
    }

    private function fitLength(string $text, string $length): string
    {
        $limit = match ($length) {
            'short' => 280,
            'long' => 1300,
            default => 700,
        };

        return Str::limit($text, $limit, '');
    }

    private function normalizeDrivers(array $drivers): array
    {
        $drivers = array_values(array_filter(array_map('trim', $drivers)));

        if ($drivers === []) {
            $drivers = config('postsmith.drivers');
        }

        return array_slice($drivers, 0, 5);
    }

    private function sortByDrivers(array $posts, array $drivers): array
    {
        return collect($posts)
            ->sortBy(fn (array $post) => array_search($post['driver'] ?? '', $drivers, true) === false ? 999 : array_search($post['driver'], $drivers, true))
            ->values()
            ->all();
    }
}
