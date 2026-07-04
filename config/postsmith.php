<?php

return [
    'brand' => [
        'name' => 'PostSmith',
        'primary' => '#4f46e5',
        'primary_dark' => '#4338ca',
        'accent' => '#7c3aed',
        'surface' => '#f8fafc',
        'ink' => '#1f2937',
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'groq'),
        'api_key' => match (env('AI_PROVIDER', 'groq')) {
            'deepseek' => env('DEEPSEEK_API_KEY', env('API_KEY', '')),
            'openai' => env('OPENAI_API_KEY', env('API_KEY', '')),
            'gemini' => env('GEMINI_API_KEY', env('API_KEY', '')),
            default => env('GROQ_API_KEY', env('API_KEY', '')),
        },
        'endpoints' => [
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'deepseek' => 'https://api.deepseek.com/v1/chat/completions',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
        ],
    ],

    'tiers' => [
        'free' => ['monthly_price' => 0, 'annual_price' => 0, 'generations' => 20, 'viral_lab' => 1, 'history_days' => 7, 'label' => 'Free'],
        'starter' => ['monthly_price' => 9, 'checkout_monthly_price' => 4.50, 'annual_price' => 86, 'checkout_annual_price' => 86, 'generations' => 200, 'viral_lab' => 5, 'history_days' => 90, 'label' => 'Starter'],
        'pro' => ['monthly_price' => 15, 'checkout_monthly_price' => 7.50, 'annual_price' => 144, 'checkout_annual_price' => 144, 'generations' => 1000, 'viral_lab' => -1, 'history_days' => -1, 'label' => 'Pro'],
    ],

    'drivers' => [
        'Real Talk',
        'Open Up',
        'Teaser',
        'Free Gift',
        'Story',
    ],

    'generator' => [
        'platforms' => [
            'LinkedIn',
            'Facebook',
            'Instagram',
            'X',
        ],
        'goals' => [
            'Get Engagement',
            'Generate Leads',
            'Increase Sales',
            'Build Brand Awareness',
            'Drive Website Traffic',
            'Grow Followers',
        ],
        'lengths' => [
            'short' => ['label' => 'Short', 'description' => 'Quick hit (2–3 concise paragraphs)'],
            'medium' => ['label' => 'Medium', 'description' => 'Natural flow'],
            'long' => ['label' => 'Long', 'description' => 'Full story / deep dive'],
        ],
        'defaults' => [
            'platform' => 'LinkedIn',
            'goal' => 'Get Engagement',
            'length' => 'medium',
        ],
    ],

    'platform_rules' => [
        'linkedin' => [
            'writing_style' => [
                'tone' => 'Professional and authoritative',
                'voice' => 'Thought leadership',
                'paragraphs' => 'Use short, easy-to-scan paragraphs',
            ],
            'engagement' => [
                'hook' => 'Begin with a strong insight, opinion or question.',
                'cta' => 'Encourage meaningful discussion in the comments.',
            ],
            'formatting' => [
                'spacing' => 'Leave white space between paragraphs.',
                'hashtags' => 'Use only relevant hashtags.',
            ],
        ],
        'facebook' => [
            'writing_style' => [
                'tone' => 'Conversational and approachable',
                'voice' => 'Community-focused',
                'paragraphs' => 'Natural, flowing conversation',
            ],
            'engagement' => [
                'hook' => 'Start with something relatable or surprising.',
                'cta' => 'Encourage reactions, shares, and community discussion.',
            ],
            'formatting' => [
                'spacing' => 'Use line breaks for readability.',
                'emoji' => 'Emojis are welcome and encourage engagement.',
            ],
        ],
        'instagram' => [
            'writing_style' => [
                'tone' => 'Authentic and emotionally engaging',
                'voice' => 'Visual storyteller',
                'paragraphs' => 'Poetic or punchy captions',
            ],
            'engagement' => [
                'hook' => 'Grab attention in the first line.',
                'cta' => 'Encourage saves and comments through engaging calls-to-action.',
            ],
            'formatting' => [
                'emoji' => 'Use emojis freely for visual interest.',
                'hashtags' => 'Strategic hashtags are essential.',
                'line_breaks' => 'Use line breaks creatively.',
            ],
        ],
        'x' => [
            'writing_style' => [
                'tone' => 'Concise and impactful',
                'voice' => 'Direct and quotable',
                'brevity' => 'Every word must earn its place.',
            ],
            'engagement' => [
                'hook' => 'The first sentence is critical; make it shareable.',
                'cta' => 'Encourage retweets and replies through strong opinions or questions.',
            ],
            'formatting' => [
                'character_limit' => 'Be aware of character constraints.',
                'threads' => 'Consider breaking ideas into threads.',
            ],
        ],
    ],

    'goal_rules' => [
        'Get Engagement' => [
            'hook' => 'Spark curiosity with a surprising insight, bold opinion, or compelling question.',
            'body' => 'Present ideas that invite discussion and different perspectives.',
            'ending' => 'Ask an open-ended question that encourages thoughtful responses.',
        ],
        'Generate Leads' => [
            'hook' => 'Identify a pain point or challenge your audience faces.',
            'body' => 'Demonstrate expertise and offer helpful insights without giving everything away.',
            'ending' => 'Invite readers to learn more, contact you, or take a specific next step.',
        ],
        'Increase Sales' => [
            'hook' => 'Highlight the benefits or transformation your product/service provides.',
            'body' => 'Explain the value clearly and address common objections.',
            'ending' => 'Include a persuasive call-to-action that makes the next step obvious.',
        ],
        'Build Brand Awareness' => [
            'hook' => 'Create genuine interest with a memorable story or unique perspective.',
            'body' => 'Tell stories that reinforce your brand values and personality.',
            'ending' => 'Reinforce the brand message and make it easy to remember you.',
        ],
        'Drive Website Traffic' => [
            'hook' => 'Create curiosity or promise useful information.',
            'body' => 'Tease valuable content or insights available on your website.',
            'ending' => 'Encourage visiting your website with a clear reason to click.',
        ],
        'Grow Followers' => [
            'hook' => 'Provide immediate value or a strong reason to follow you.',
            'body' => 'Establish credibility and show what followers can expect.',
            'ending' => 'Encourage following for more content of this quality.',
        ],
    ],

    'length_rules' => [
        'short' => [
            'description' => 'Quick hit (2–3 concise paragraphs)',
            'guidance' => [
                'Keep the message concise and direct.',
                'Focus on one main idea.',
                'Avoid unnecessary explanation.',
                'Easy to consume quickly.',
            ],
        ],
        'medium' => [
            'description' => 'Natural flow (8–12 lines)',
            'guidance' => [
                'Develop one clear idea with supporting context.',
                'Maintain a comfortable reading pace.',
                'Balance brevity and depth.',
                'Use paragraph breaks for readability.',
            ],
        ],
        'long' => [
            'description' => 'Full story / deep dive (5–7 paragraphs)',
            'guidance' => [
                'Tell a complete story or explore an idea deeply.',
                'Build emotional connection with readers.',
                'Use well-structured paragraphs and transitions.',
                'Provide comprehensive insight or narrative.',
            ],
        ],
    ],

    'viral_lab' => [
        'min_words' => 30,
        'min_likes' => 50,
        'min_comments' => 15,
        'min_shares' => 5,
    ],

    'payments' => [
        'flutterwave_public_key' => env('FLW_PUBLIC_KEY', ''),
        'flutterwave_secret_key' => env('FLW_SECRET_KEY', ''),
        'flutterwave_webhook_secret_hash' => env('FLW_SECRET_HASH', ''),
        'currency' => env('FLW_CURRENCY', 'USD'),
        'flutterwave_payment_plans' => [
            'starter' => [
                'monthly' => env('FLW_STARTER_MONTHLY_PLAN_ID', ''),
                'annual' => env('FLW_STARTER_ANNUAL_PLAN_ID', ''),
            ],
            'pro' => [
                'monthly' => env('FLW_PRO_MONTHLY_PLAN_ID', ''),
                'annual' => env('FLW_PRO_ANNUAL_PLAN_ID', ''),
            ],
        ],
    ],

    'admin_email' => env('ADMIN_EMAIL', ''),
    'admin_emails' => array_values(array_filter(array_map('trim', explode(',', env('ADMIN_EMAILS', ''))))),
    'sender_email' => env('SENDER_EMAIL', 'onboarding@resend.dev'),
    'app_domain' => rtrim(env('APP_DOMAIN', env('APP_URL', 'http://localhost')), '/') . '/',
];
