<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'cesga' => [
        'base_url' => env('CESGA_API_URL', 'https://api-mock-cesga.onrender.com'),
        'timeout' => (int) env('CESGA_API_TIMEOUT', 45),
    ],

    'llm' => [
        'timeout' => (int) env('LLM_TIMEOUT', 60),
        'anthropic' => [
            'base_url' => env('ANTHROPIC_API_URL', 'https://api.anthropic.com'),
            'default_model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-sonnet-4-5'),
            'models' => [
                'claude-opus-4-5',
                'claude-sonnet-4-5',
                'claude-haiku-4-5',
            ],
        ],
        'openai' => [
            'base_url' => env('OPENAI_API_URL', 'https://api.openai.com'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o-mini'),
            'models' => [
                'gpt-4o',
                'gpt-4o-mini',
                'o1-mini',
            ],
        ],
        'gemini' => [
            'base_url' => env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com'),
            'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-2.0-flash'),
            'models' => [
                'gemini-2.0-flash',
                'gemini-1.5-pro',
                'gemini-1.5-flash',
            ],
        ],
    ],

];
