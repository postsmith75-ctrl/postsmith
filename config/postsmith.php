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
