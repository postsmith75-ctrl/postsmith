<?php

namespace Database\Seeders;

use App\Models\DiscoveredDriver;
use App\Models\TrainingExample;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DiscoveredDriver::query()->upsert([
            [
                'driver_name' => 'Pattern Interrupt',
                'description' => 'Break expected patterns to create cognitive dissonance that demands resolution.',
                'psychology' => 'The brain hates unfinished patterns. Interrupting the expected flow forces the reader to stop and process.',
                'submissions_count' => 1,
                'avg_confidence' => 85,
                'status' => 'active',
                'promoted_at' => now(),
                'new_until' => now()->addDays(14),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_name' => 'Authority Flip',
                'description' => 'Establish credibility by admitting a limitation or counter-intuitive truth.',
                'psychology' => 'Vulnerability from an expert is more trustworthy than perfection. It creates parasocial trust.',
                'submissions_count' => 1,
                'avg_confidence' => 85,
                'status' => 'active',
                'promoted_at' => now(),
                'new_until' => now()->addDays(14),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_name' => 'Curiosity Gap',
                'description' => 'Reveal enough to create an information gap, but withhold the resolution.',
                'psychology' => 'People remember unfinished loops and want to close them.',
                'submissions_count' => 1,
                'avg_confidence' => 85,
                'status' => 'active',
                'promoted_at' => now(),
                'new_until' => now()->addDays(14),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_name' => 'Identity Mirror',
                'description' => "Reflect the reader's self-image back at them with precision.",
                'psychology' => 'When people see themselves described accurately, they feel understood and engage.',
                'submissions_count' => 1,
                'avg_confidence' => 85,
                'status' => 'active',
                'promoted_at' => now(),
                'new_until' => now()->addDays(14),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['driver_name']);

        $examples = [
            [
                'driver_name' => 'Real Talk',
                'raw_thought' => 'I just realized most people give up right before the breakthrough',
                'transformed_post' => 'Real talk: most people quit when the work finally starts compounding. The breakthrough usually looks boring right before it looks obvious.',
                'platform' => 'general',
                'source' => 'curated',
                'engagement_score' => 90,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_name' => 'Open Up',
                'raw_thought' => 'I almost gave up on social media',
                'transformed_post' => 'I almost deleted everything last year. Low reach, no replies, no momentum. Then I stopped trying to sound impressive and started writing what I actually meant.',
                'platform' => 'general',
                'source' => 'curated',
                'engagement_score' => 86,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_name' => 'Teaser',
                'raw_thought' => 'there is a reason some posts go viral and others do not',
                'transformed_post' => 'I studied why some posts travel and others disappear. The difference was not timing, hashtags, or luck. It was the first unresolved idea.',
                'platform' => 'general',
                'source' => 'curated',
                'engagement_score' => 88,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_name' => 'Story',
                'raw_thought' => 'I messed up a presentation yesterday and learned something',
                'transformed_post' => 'I bombed a presentation yesterday. My hands shook. My voice cracked. But the room taught me something useful about turning pressure into better stories.',
                'platform' => 'general',
                'source' => 'curated',
                'engagement_score' => 92,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($examples as $example) {
            TrainingExample::query()->updateOrCreate(
                [
                    'driver_name' => $example['driver_name'],
                    'raw_thought' => $example['raw_thought'],
                    'platform' => $example['platform'],
                ],
                $example
            );
        }
    }
}
