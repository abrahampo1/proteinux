<?php

use App\Support\Ai\AiSettings;

it('shows the settings page', function () {
    $response = $this->get('/ajustes-ia');

    $response->assertOk();
    $response->assertSee('Ajustes de IA');
    $response->assertSee('Anthropic');
    $response->assertSee('OpenAI');
    $response->assertSee('Google Gemini');
});

it('stores an api key in the session (encrypted) and marks provider configured', function () {
    $this->withSession([])
        ->post('/ajustes-ia', [
            'active_provider' => 'anthropic',
            'providers' => [
                'anthropic' => ['api_key' => 'sk-ant-secret-1234', 'model' => 'claude-sonnet-4-5'],
            ],
        ])
        ->assertRedirect('/ajustes-ia');

    $settings = app(AiSettings::class);

    expect($settings->isConfigured())->toBeTrue()
        ->and($settings->activeProvider())->toBe('anthropic')
        ->and($settings->apiKeyFor('anthropic'))->toBe('sk-ant-secret-1234')
        ->and($settings->modelFor('anthropic'))->toBe('claude-sonnet-4-5');
});

it('preserves an existing key when the form submits an empty value', function () {
    $settings = app(AiSettings::class);
    $settings->save([
        'active_provider' => 'openai',
        'providers' => [
            'openai' => ['api_key' => 'sk-original', 'model' => 'gpt-4o-mini'],
        ],
    ]);

    $this->post('/ajustes-ia', [
        'active_provider' => 'openai',
        'providers' => [
            'openai' => ['api_key' => '', 'model' => 'gpt-4o'],
        ],
    ])->assertRedirect('/ajustes-ia');

    expect($settings->apiKeyFor('openai'))->toBe('sk-original')
        ->and($settings->modelFor('openai'))->toBe('gpt-4o');
});

it('clears a single provider via delete', function () {
    $settings = app(AiSettings::class);
    $settings->save([
        'active_provider' => 'gemini',
        'providers' => [
            'gemini' => ['api_key' => 'goog-secret', 'model' => 'gemini-2.0-flash'],
        ],
    ]);

    expect($settings->hasKeyFor('gemini'))->toBeTrue();

    $this->delete('/ajustes-ia/gemini')->assertRedirect('/ajustes-ia');

    expect($settings->hasKeyFor('gemini'))->toBeFalse()
        ->and($settings->activeProvider())->toBeNull();
});

it('rejects an invalid provider', function () {
    $this->post('/ajustes-ia', [
        'active_provider' => 'bogus',
        'providers' => [],
    ])->assertSessionHasErrors('active_provider');
});
