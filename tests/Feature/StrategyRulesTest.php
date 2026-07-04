<?php

namespace Tests\Feature;

use App\Data\StrategyContext;
use App\Services\Postsmith\StrategyRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrategyRulesTest extends TestCase
{
    private StrategyRules $strategyRules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategyRules = new StrategyRules();
    }

    public function test_resolve_returns_strategy_context(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'medium');

        $this->assertInstanceOf(StrategyContext::class, $context);
    }

    public function test_strategy_selection_contains_correct_data(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'medium');

        $this->assertEquals('linkedin', $context->strategy->platform_key);
        $this->assertEquals('LinkedIn', $context->strategy->platform_label);
        $this->assertEquals('Get Engagement', $context->strategy->goal_key);
        $this->assertEquals('Get Engagement', $context->strategy->goal_label);
        $this->assertEquals('medium', $context->strategy->length_key);
        $this->assertEquals('medium', $context->strategy->length_label);
    }

    public function test_platform_rules_load_correctly_for_linkedin(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'medium');

        $this->assertIsArray($context->platformRules);
        $this->assertArrayHasKey('writing_style', $context->platformRules);
        $this->assertArrayHasKey('engagement', $context->platformRules);
        $this->assertArrayHasKey('formatting', $context->platformRules);
        $this->assertEquals('Professional and authoritative', $context->platformRules['writing_style']['tone']);
    }

    public function test_platform_rules_load_correctly_for_facebook(): void
    {
        $context = $this->strategyRules->resolve('Facebook', 'Get Engagement', 'medium');

        $this->assertIsArray($context->platformRules);
        $this->assertEquals('Conversational and approachable', $context->platformRules['writing_style']['tone']);
        $this->assertEquals('facebook', $context->strategy->platform_key);
    }

    public function test_platform_rules_load_correctly_for_instagram(): void
    {
        $context = $this->strategyRules->resolve('Instagram', 'Get Engagement', 'medium');

        $this->assertIsArray($context->platformRules);
        $this->assertEquals('Authentic and emotionally engaging', $context->platformRules['writing_style']['tone']);
        $this->assertEquals('instagram', $context->strategy->platform_key);
    }

    public function test_platform_rules_load_correctly_for_x(): void
    {
        $context = $this->strategyRules->resolve('X', 'Get Engagement', 'medium');

        $this->assertIsArray($context->platformRules);
        $this->assertEquals('Concise and impactful', $context->platformRules['writing_style']['tone']);
        $this->assertEquals('x', $context->strategy->platform_key);
    }

    public function test_goal_rules_load_correctly_for_get_engagement(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'medium');

        $this->assertIsArray($context->goalRules);
        $this->assertEquals('Get Engagement', $context->strategy->goal_key);
        $this->assertStringContainsString('curiosity', strtolower($context->goalRules['hook']));
    }

    public function test_goal_rules_load_correctly_for_generate_leads(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Generate Leads', 'medium');

        $this->assertIsArray($context->goalRules);
        $this->assertEquals('Generate Leads', $context->strategy->goal_key);
        $this->assertStringContainsString('pain point', strtolower($context->goalRules['hook']));
    }

    public function test_goal_rules_load_correctly_for_increase_sales(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Increase Sales', 'medium');

        $this->assertIsArray($context->goalRules);
        $this->assertEquals('Increase Sales', $context->strategy->goal_key);
        $this->assertStringContainsString('benefits', strtolower($context->goalRules['hook']));
    }

    public function test_goal_rules_load_correctly_for_build_brand_awareness(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Build Brand Awareness', 'medium');

        $this->assertIsArray($context->goalRules);
        $this->assertEquals('Build Brand Awareness', $context->strategy->goal_key);
    }

    public function test_goal_rules_load_correctly_for_drive_website_traffic(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Drive Website Traffic', 'medium');

        $this->assertIsArray($context->goalRules);
        $this->assertEquals('Drive Website Traffic', $context->strategy->goal_key);
    }

    public function test_goal_rules_load_correctly_for_grow_followers(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Grow Followers', 'medium');

        $this->assertIsArray($context->goalRules);
        $this->assertEquals('Grow Followers', $context->strategy->goal_key);
    }

    public function test_length_rules_load_correctly_for_short(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'short');

        $this->assertIsArray($context->lengthRules);
        $this->assertEquals('short', $context->strategy->length_key);
        $this->assertArrayHasKey('description', $context->lengthRules);
        $this->assertArrayHasKey('guidance', $context->lengthRules);
        $this->assertIsArray($context->lengthRules['guidance']);
        $this->assertNotEmpty($context->lengthRules['guidance']);
    }

    public function test_length_rules_load_correctly_for_medium(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'medium');

        $this->assertIsArray($context->lengthRules);
        $this->assertEquals('medium', $context->strategy->length_key);
    }

    public function test_length_rules_load_correctly_for_long(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'long');

        $this->assertIsArray($context->lengthRules);
        $this->assertEquals('long', $context->strategy->length_key);
    }

    public function test_invalid_platform_falls_back_to_default(): void
    {
        $context = $this->strategyRules->resolve('UnknownPlatform', 'Get Engagement', 'medium');

        $this->assertInstanceOf(StrategyContext::class, $context);
        $this->assertEquals('linkedin', $context->strategy->platform_key);
        $this->assertEquals('LinkedIn', $context->strategy->platform_label);
    }

    public function test_invalid_goal_falls_back_to_default(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'UnknownGoal', 'medium');

        $this->assertInstanceOf(StrategyContext::class, $context);
        $this->assertEquals('Get Engagement', $context->strategy->goal_key);
    }

    public function test_invalid_length_falls_back_to_default(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'unknown');

        $this->assertInstanceOf(StrategyContext::class, $context);
        $this->assertEquals('medium', $context->strategy->length_key);
    }

    public function test_strategy_context_is_complete(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'medium');

        $this->assertNotNull($context->strategy);
        $this->assertNotNull($context->platformRules);
        $this->assertNotNull($context->goalRules);
        $this->assertNotNull($context->lengthRules);
    }

    public function test_strategy_context_never_throws_exception(): void
    {
        $combinations = [
            ['platform' => 'linkedin', 'goal' => 'Get Engagement', 'length' => 'short'],
            ['platform' => 'INVALID', 'goal' => 'INVALID', 'length' => 'INVALID'],
            ['platform' => '', 'goal' => '', 'length' => ''],
            ['platform' => 'Facebook', 'goal' => 'Generate Leads', 'length' => 'long'],
        ];

        foreach ($combinations as $combo) {
            try {
                $context = $this->strategyRules->resolve($combo['platform'], $combo['goal'], $combo['length']);
                $this->assertInstanceOf(StrategyContext::class, $context);
            } catch (\Exception $e) {
                $this->fail("StrategyRules threw an exception: {$e->getMessage()}");
            }
        }
    }

    public function test_platform_rules_include_all_sections(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'medium');

        $this->assertArrayHasKey('writing_style', $context->platformRules);
        $this->assertArrayHasKey('engagement', $context->platformRules);
        $this->assertArrayHasKey('formatting', $context->platformRules);
    }

    public function test_goal_rules_include_hook_body_ending(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'medium');

        $this->assertArrayHasKey('hook', $context->goalRules);
        $this->assertArrayHasKey('body', $context->goalRules);
        $this->assertArrayHasKey('ending', $context->goalRules);
    }

    public function test_length_rules_guidance_is_array(): void
    {
        $context = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'short');

        $this->assertArrayHasKey('guidance', $context->lengthRules);
        $this->assertIsArray($context->lengthRules['guidance']);
        $this->assertGreaterThan(0, count($context->lengthRules['guidance']));

        foreach ($context->lengthRules['guidance'] as $item) {
            $this->assertIsString($item);
        }
    }

    public function test_case_insensitivity_for_length(): void
    {
        $contextShort = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'Short');
        $contextMedium = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'MEDIUM');
        $contextLong = $this->strategyRules->resolve('LinkedIn', 'Get Engagement', 'LoNg');

        $this->assertEquals('short', $contextShort->strategy->length_key);
        $this->assertEquals('medium', $contextMedium->strategy->length_key);
        $this->assertEquals('long', $contextLong->strategy->length_key);
    }
}
