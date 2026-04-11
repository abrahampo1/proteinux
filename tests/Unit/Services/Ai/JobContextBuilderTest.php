<?php

use App\Services\Ai\JobContextBuilder;

it('serializes a complete outputs payload into a text block', function () {
    $outputs = [
        'protein_metadata' => [
            'protein_name' => 'Hemoglobina alfa',
            'organism' => 'Homo sapiens',
            'uniprot_id' => 'P69905',
            'pdb_id' => '1A3N',
        ],
        'structural_data' => [
            'confidence' => [
                'plddt_mean' => 87.3,
                'mean_pae' => 4.2,
                'plddt_histogram' => [
                    'very_high' => 62,
                    'high' => 28,
                    'medium' => 8,
                    'low' => 2,
                ],
                'plddt_per_residue' => array_fill(0, 100, 85),
            ],
        ],
        'biological_data' => [
            'solubility_score' => 78.0,
            'solubility_prediction' => 'soluble',
            'instability_index' => 32.1,
            'stability_status' => 'stable',
            'toxicity_alerts' => [],
            'allergenicity_alerts' => [],
            'secondary_structure_prediction' => [
                'helix_percent' => 75,
                'strand_percent' => 0,
                'coil_percent' => 25,
            ],
        ],
    ];

    $text = JobContextBuilder::build($outputs);

    expect($text)
        ->toContain('Hemoglobina alfa')
        ->toContain('Homo sapiens')
        ->toContain('UniProt P69905')
        ->toContain('PDB 1A3N')
        ->toContain('pLDDT medio: 87.3')
        ->toContain('PAE medio: 4.20')
        ->toContain('muy alta (>90) 62.0%')
        ->toContain('Solubilidad: 78.0')
        ->toContain('soluble')
        ->toContain('Índice de inestabilidad: 32.1')
        ->toContain('stable')
        ->toContain('toxicidad: ninguna')
        ->toContain('alergenicidad: ninguna')
        ->toContain('α-hélice 75%');
});

it('detects contiguous low-confidence regions', function () {
    $perResidue = array_merge(
        array_fill(0, 10, 90),  // 1-10 high
        array_fill(0, 5, 50),   // 11-15 low
        array_fill(0, 10, 85),  // 16-25 high
        array_fill(0, 3, 40),   // 26-28 low
    );

    $outputs = [
        'structural_data' => [
            'confidence' => [
                'plddt_mean' => 75,
                'plddt_per_residue' => $perResidue,
            ],
        ],
    ];

    $text = JobContextBuilder::build($outputs);

    expect($text)
        ->toContain('11-15')
        ->toContain('26-28');
});

it('handles missing fields gracefully', function () {
    $text = JobContextBuilder::build(['protein_metadata' => ['protein_name' => 'X']]);

    expect($text)->toContain('Nombre: X');
});

it('includes accounting when provided', function () {
    $outputs = ['protein_metadata' => ['protein_name' => 'Y']];
    $accounting = [
        'accounting' => [
            'gpu_hours' => 0.5,
            'cpu_hours' => 4.0,
            'total_wall_time_seconds' => 1800,
        ],
    ];

    $text = JobContextBuilder::build($outputs, $accounting);

    expect($text)
        ->toContain('RECURSOS HPC')
        ->toContain('Horas GPU: 0.5000')
        ->toContain('Tiempo de pared: 1800s');
});
