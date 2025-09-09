<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Source Language
    |--------------------------------------------------------------------------
    |
    | The default source language for translations (ISO 639-1 code).
    | Example: 'en' for English.
    |
    */

    'source_language' => env('TRANSLATION_SOURCE_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Translation Provider Configuration
    |--------------------------------------------------------------------------
    |
    | These options configure the translation provider (e.g., OpenAI).
    | You may define the provider name, API base URI, authentication key,
    | model, token limits, and temperature settings.
    |
    */

    'provider' => [
        'name'        => env('TRANSLATION_PROVIDER', 'openai'),
        'base_uri'    => env('OPENAI_BASE_URI', 'https://api.openai.com/v1'),
        'api_key'     => env('OPENAI_API_KEY'),
        'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'max_tokens'  => (int) env('OPENAI_TOKENS', 1000),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.0),
    ],

];
