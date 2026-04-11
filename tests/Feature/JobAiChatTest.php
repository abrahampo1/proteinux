<?php

use App\Services\CesgaApiService;
use App\Support\Ai\AiSettings;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->mock(CesgaApiService::class, function ($mock) {
        $mock->shouldReceive('getJobOutputs')->andReturn([
            'protein_metadata' => ['protein_name' => 'X', 'organism' => 'Y'],
            'structural_data' => ['confidence' => ['plddt_mean' => 80]],
            'biological_data' => [
                'solubility_score' => 50, 'solubility_prediction' => 'soluble',
                'instability_index' => 30, 'stability_status' => 'stable',
                'toxicity_alerts' => [], 'allergenicity_alerts' => [],
            ],
        ]);
    });
});

it('returns 409 when provider not configured', function () {
    $this->postJson('/api/jobs/test/ai-chat', [
        'messages' => [['role' => 'user', 'content' => 'hola']],
    ])->assertStatus(409);
});

it('validates that the last message is from the user', function () {
    app(AiSettings::class)->save([
        'active_provider' => 'anthropic',
        'providers' => ['anthropic' => ['api_key' => 'k', 'model' => 'm']],
    ]);

    $this->postJson('/api/jobs/test/ai-chat', [
        'messages' => [
            ['role' => 'user', 'content' => 'primera'],
            ['role' => 'assistant', 'content' => 'respuesta'],
        ],
    ])->assertStatus(422);
});

it('returns a reply from openai', function () {
    app(AiSettings::class)->save([
        'active_provider' => 'openai',
        'providers' => ['openai' => ['api_key' => 'sk-test', 'model' => 'gpt-4o-mini']],
    ]);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['role' => 'assistant', 'content' => 'Porque el dominio tiene baja pLDDT.']],
            ],
        ]),
    ]);

    $this->postJson('/api/jobs/test/ai-chat', [
        'messages' => [['role' => 'user', 'content' => '¿Por qué el PAE es alto?']],
    ])
        ->assertOk()
        ->assertJsonPath('reply', 'Porque el dominio tiene baja pLDDT.')
        ->assertJsonPath('provider', 'openai');
});

it('rejects malformed messages', function () {
    app(AiSettings::class)->save([
        'active_provider' => 'gemini',
        'providers' => ['gemini' => ['api_key' => 'k', 'model' => 'm']],
    ]);

    $this->postJson('/api/jobs/test/ai-chat', [
        'messages' => [['role' => 'robot', 'content' => 'x']],
    ])->assertStatus(422);
});

it('returns a reply from gemini', function () {
    app(AiSettings::class)->save([
        'active_provider' => 'gemini',
        'providers' => ['gemini' => ['api_key' => 'g-key', 'model' => 'gemini-2.0-flash']],
    ]);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                ['content' => ['parts' => [['text' => 'Respuesta de Gemini.']]]],
            ],
        ]),
    ]);

    $this->postJson('/api/jobs/test/ai-chat', [
        'messages' => [['role' => 'user', 'content' => 'pregunta']],
    ])
        ->assertOk()
        ->assertJsonPath('reply', 'Respuesta de Gemini.');
});
