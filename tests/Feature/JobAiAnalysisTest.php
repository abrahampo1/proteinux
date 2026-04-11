<?php

use App\Services\CesgaApiService;
use App\Support\Ai\AiSettings;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $fakeOutputs = [
        'protein_metadata' => [
            'protein_name' => 'Test Protein',
            'organism' => 'E. coli',
        ],
        'structural_data' => [
            'confidence' => [
                'plddt_mean' => 80.5,
                'mean_pae' => 5.0,
                'plddt_histogram' => ['very_high' => 40, 'high' => 40, 'medium' => 15, 'low' => 5],
            ],
        ],
        'biological_data' => [
            'solubility_score' => 60,
            'solubility_prediction' => 'soluble',
            'instability_index' => 35,
            'stability_status' => 'stable',
            'toxicity_alerts' => [],
            'allergenicity_alerts' => [],
        ],
    ];

    $this->mock(CesgaApiService::class, function ($mock) use ($fakeOutputs) {
        $mock->shouldReceive('getJobOutputs')->andReturn($fakeOutputs);
        $mock->shouldReceive('getJobAccounting')->andReturn([
            'accounting' => ['gpu_hours' => 0.1, 'cpu_hours' => 1, 'total_wall_time_seconds' => 60],
        ]);
    });
});

it('returns 409 when no provider is configured', function () {
    $this->getJson('/api/jobs/test-id/ai-analysis')
        ->assertStatus(409)
        ->assertJson(['code' => 'not_configured']);
});

it('returns an analysis when anthropic is configured', function () {
    app(AiSettings::class)->save([
        'active_provider' => 'anthropic',
        'providers' => [
            'anthropic' => ['api_key' => 'sk-ant-test', 'model' => 'claude-sonnet-4-5'],
        ],
    ]);

    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'Análisis de prueba: pLDDT alto, soluble.']],
        ]),
    ]);

    $this->getJson('/api/jobs/test-id/ai-analysis')
        ->assertOk()
        ->assertJson([
            'analysis' => 'Análisis de prueba: pLDDT alto, soluble.',
            'provider' => 'anthropic',
            'model' => 'claude-sonnet-4-5',
        ]);

    Http::assertSent(function ($request) {
        return $request->hasHeader('x-api-key', 'sk-ant-test')
            && str_contains((string) $request->url(), '/v1/messages');
    });
});

it('surfaces auth errors from the provider', function () {
    app(AiSettings::class)->save([
        'active_provider' => 'openai',
        'providers' => [
            'openai' => ['api_key' => 'sk-bad', 'model' => 'gpt-4o-mini'],
        ],
    ]);

    Http::fake([
        'api.openai.com/*' => Http::response(['error' => ['message' => 'invalid']], 401),
    ]);

    $this->getJson('/api/jobs/test-id/ai-analysis')
        ->assertStatus(401)
        ->assertJsonPath('code', 'provider_error');
});
