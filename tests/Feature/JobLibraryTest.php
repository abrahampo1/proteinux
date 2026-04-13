<?php

use App\Exceptions\CesgaApiException;
use App\Models\PredictedJob;
use App\Models\User;
use App\Services\CesgaApiService;
use App\Services\JobLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->fastaHemo = ">sp|P69905|HBA_HUMAN\nMVLSPADKTNVKAAWGKVGAHAGEYGAEALERMFLSFPTTKTYFPHFDLSHGSAQVKGHG\n";

    $this->fakeOutputs = [
        'protein_metadata' => [
            'protein_name' => 'Hemoglobin alpha',
            'organism' => 'Homo sapiens',
            'uniprot_id' => 'P69905',
            'pdb_id' => '1A3N',
        ],
        'structural_data' => [
            'confidence' => [
                'plddt_mean' => 87.3,
                'plddt_per_residue' => array_fill(0, 141, 87),
                'mean_pae' => 4.2,
                'plddt_histogram' => ['very_high' => 60, 'high' => 30, 'medium' => 8, 'low' => 2],
            ],
            'pdb_file' => 'ATOM...',
            'cif_file' => '',
        ],
        'biological_data' => [
            'solubility_score' => 70,
            'solubility_prediction' => 'soluble',
            'instability_index' => 30,
            'stability_status' => 'stable',
            'toxicity_alerts' => [],
            'allergenicity_alerts' => [],
            'secondary_structure_prediction' => ['helix_percent' => 75, 'strand_percent' => 0, 'coil_percent' => 25],
        ],
    ];
});

it('computes the same hash for sequences that only differ in whitespace/case', function () {
    $library = app(JobLibrary::class);
    $a = ">header\nMVLSPADKTNVKAAWGKVGAHAGEYGAEALERMFLSFPTTKTYFPHFDLSHGSAQVKGHG\n";
    $b = "> another header\n  mvlspadktn  vkaawgkvgahageygaealermflsfpttktyfphfdlshgsaqvkghg\n";
    $c = ">alt\nMVLSPADKTNVKAAWGKVGAHAGEYGAEALERMFLSFPTTKTYFPHFDLSHGSAQVKGHG\nEXTRA\n";

    expect($library->hashFasta($a))->toBe($library->hashFasta($b));
    expect($library->hashFasta($a))->not->toBe($library->hashFasta($c));
});

it('registers a submission in the library with a preview', function () {
    $library = app(JobLibrary::class);
    $row = $library->registerSubmission($this->fastaHemo, 'job-123', 'hemo.fasta');

    expect($row)->toBeInstanceOf(PredictedJob::class)
        ->and($row->job_id)->toBe('job-123')
        ->and($row->fasta_filename)->toBe('hemo.fasta')
        ->and($row->sequence_length)->toBeGreaterThan(50)
        ->and($row->fasta_preview)->toStartWith('MVLSPADKTNV');
});

it('enriches a library row from CESGA outputs', function () {
    $library = app(JobLibrary::class);
    $library->registerSubmission($this->fastaHemo, 'job-123');

    $row = $library->enrichFromOutputs('job-123', $this->fakeOutputs);

    expect($row)->not->toBeNull()
        ->and($row->protein_name)->toBe('Hemoglobin alpha')
        ->and($row->organism)->toBe('Homo sapiens')
        ->and($row->uniprot_id)->toBe('P69905')
        ->and($row->pdb_id)->toBe('1A3N')
        ->and($row->plddt_mean)->toBe(87.3)
        ->and($row->completed_at)->not->toBeNull();
});

it('enrichFromOutputs is a noop when the job_id is unknown', function () {
    $library = app(JobLibrary::class);

    expect($library->enrichFromOutputs('ghost-job', $this->fakeOutputs))->toBeNull();
});

it('redirects to the existing job when a known FASTA is re-submitted', function () {
    $this->mock(CesgaApiService::class, function ($mock) {
        // El lookup stale-check llama a getJobStatus — tiene que pasar.
        $mock->shouldReceive('getJobStatus')->with('existing-123')->andReturn(['status' => 'COMPLETED']);
        // Nunca se debe llamar a submitJob en este flujo.
        $mock->shouldNotReceive('submitJob');
    });

    app(JobLibrary::class)->registerSubmission($this->fastaHemo, 'existing-123');

    $response = $this->actingAs(User::factory()->create())->post('/jobs', [
        'fasta_sequence' => $this->fastaHemo,
        'fasta_filename' => 'hemo.fasta',
    ]);

    $response->assertRedirect('/jobs/existing-123');
    $response->assertSessionHas('info');

    // Sólo debe existir UNA row en la biblioteca para esta secuencia.
    expect(PredictedJob::count())->toBe(1);
});

it('bypasses the library when force=1 is sent', function () {
    $this->mock(CesgaApiService::class, function ($mock) {
        // No debe mirar el status del existente porque no comprueba dedup.
        $mock->shouldNotReceive('getJobStatus');
        $mock->shouldReceive('submitJob')->andReturn(['job_id' => 'new-999']);
    });

    app(JobLibrary::class)->registerSubmission($this->fastaHemo, 'old-123');

    $this->actingAs(User::factory()->create())->post('/jobs', [
        'fasta_sequence' => $this->fastaHemo,
        'fasta_filename' => 'hemo.fasta',
        'force' => '1',
    ])->assertRedirect('/jobs/new-999');

    // La row de la biblioteca se ha actualizado al nuevo job_id.
    expect(PredictedJob::where('sequence_hash', app(JobLibrary::class)->hashFasta($this->fastaHemo))->value('job_id'))
        ->toBe('new-999');
});

it('falls through to a fresh submission when the cached job is stale in CESGA', function () {
    $this->mock(CesgaApiService::class, function ($mock) {
        $mock->shouldReceive('getJobStatus')->with('stale-1')->andThrow(new CesgaApiException('not found', 404));
        $mock->shouldReceive('submitJob')->andReturn(['job_id' => 'fresh-2']);
    });

    app(JobLibrary::class)->registerSubmission($this->fastaHemo, 'stale-1');

    $this->actingAs(User::factory()->create())->post('/jobs', [
        'fasta_sequence' => $this->fastaHemo,
        'fasta_filename' => 'hemo.fasta',
    ])->assertRedirect('/jobs/fresh-2');

    expect(PredictedJob::where('job_id', 'stale-1')->exists())->toBeFalse();
    expect(PredictedJob::where('job_id', 'fresh-2')->exists())->toBeTrue();
});

it('lists library entries and supports search on the index page', function () {
    PredictedJob::create([
        'sequence_hash' => str_repeat('a', 64),
        'job_id' => 'job-hemo',
        'fasta_sequence' => '>hemo\nMVLSP...',
        'protein_name' => 'Hemoglobin alpha',
        'organism' => 'Homo sapiens',
        'uniprot_id' => 'P69905',
        'sequence_length' => 141,
        'plddt_mean' => 87.3,
        'completed_at' => now(),
    ]);
    PredictedJob::create([
        'sequence_hash' => str_repeat('b', 64),
        'job_id' => 'job-ubq',
        'fasta_sequence' => '>ubq\nMQIFVK...',
        'protein_name' => 'Ubiquitin',
        'organism' => 'Homo sapiens',
        'sequence_length' => 76,
        'plddt_mean' => 92.1,
        'completed_at' => now(),
    ]);

    $all = $this->get('/biblioteca');
    $all->assertOk();
    $all->assertSee('Hemoglobin alpha');
    $all->assertSee('Ubiquitin');

    $filtered = $this->get('/biblioteca?q=hemo');
    $filtered->assertOk();
    $filtered->assertSee('Hemoglobin alpha');
    $filtered->assertDontSee('Ubiquitin');
});

it('lazily enriches a pending library row when /biblioteca is visited', function () {
    $this->mock(CesgaApiService::class, function ($mock) {
        $mock->shouldReceive('getJobStatus')->with('pending-job')->andReturn(['status' => 'COMPLETED']);
        $mock->shouldReceive('getJobOutputs')->with('pending-job')->andReturn($this->fakeOutputs);
    });

    PredictedJob::create([
        'sequence_hash' => str_repeat('c', 64),
        'job_id' => 'pending-job',
        'fasta_sequence' => ">hemo\nMVLSPADK",
        'fasta_filename' => 'hemo.fasta',
        'fasta_preview' => 'MVLSPADK',
        'sequence_length' => 8,
    ]);

    $response = $this->get('/biblioteca');
    $response->assertOk();
    $response->assertSee('Hemoglobin alpha');        // nombre de la proteína enriquecido
    $response->assertDontSee('sin nombre');          // fallback ya no debe aparecer

    $row = PredictedJob::where('job_id', 'pending-job')->first();
    expect($row->plddt_mean)->toBe(87.3)
        ->and($row->protein_name)->toBe('Hemoglobin alpha')
        ->and($row->completed_at)->not->toBeNull();
});

it('shows fasta_filename as display fallback when protein_name is null', function () {
    $row = new PredictedJob([
        'job_id' => 'abc123def456',
        'fasta_filename' => 'custom_enzyme.fasta',
    ]);

    expect($row->displayName())->toBe('custom_enzyme');
});

it('falls back to a short job id only when there is no filename', function () {
    $row = new PredictedJob([
        'job_id' => 'abc123def456',
    ]);

    expect($row->displayName())->toBe('sin nombre · abc123de');
});

it('reruns a library entry with the stored FASTA', function () {
    $this->mock(CesgaApiService::class, function ($mock) {
        $mock->shouldReceive('submitJob')->once()->andReturn(['job_id' => 'rerun-99']);
    });

    $row = app(JobLibrary::class)->registerSubmission($this->fastaHemo, 'old-1', 'hemo.fasta');

    $this->actingAs(User::factory()->create())->post('/biblioteca/'.$row->id.'/rerun')
        ->assertRedirect('/jobs/rerun-99');

    $row->refresh();
    expect($row->job_id)->toBe('rerun-99')
        ->and($row->completed_at)->toBeNull()
        ->and($row->plddt_mean)->toBeNull();
});
