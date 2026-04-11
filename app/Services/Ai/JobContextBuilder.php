<?php

namespace App\Services\Ai;

class JobContextBuilder
{
    /**
     * Serialize the CESGA outputs payload into a compact text block suitable
     * for an LLM system prompt. Avoids sending large arrays (PAE matrix,
     * per-residue pLDDT) — only summary statistics.
     *
     * @param  array<string, mixed>  $outputs
     * @param  array<string, mixed>|null  $accounting
     */
    public static function build(array $outputs, ?array $accounting = null): string
    {
        $lines = [];

        $meta = $outputs['protein_metadata'] ?? [];
        if (! empty($meta)) {
            $lines[] = 'PROTEÍNA';
            if (! empty($meta['protein_name'])) {
                $lines[] = '- Nombre: '.$meta['protein_name'];
            }
            if (! empty($meta['organism'])) {
                $lines[] = '- Organismo: '.$meta['organism'];
            }
            $ids = [];
            if (! empty($meta['uniprot_id'])) {
                $ids[] = 'UniProt '.$meta['uniprot_id'];
            }
            if (! empty($meta['pdb_id'])) {
                $ids[] = 'PDB '.$meta['pdb_id'];
            }
            if ($ids !== []) {
                $lines[] = '- IDs: '.implode(' · ', $ids);
            }
            $lines[] = '';
        }

        $structural = $outputs['structural_data'] ?? [];
        $confidence = $structural['confidence'] ?? [];
        if (! empty($confidence)) {
            $lines[] = 'CONFIANZA DE LA PREDICCIÓN';
            if (isset($confidence['plddt_mean'])) {
                $plddt = (float) $confidence['plddt_mean'];
                $lines[] = '- pLDDT medio: '.self::fmt($plddt, 1).' / 100 ('.self::plddtQualitativeLabel($plddt).')';
            }
            if (isset($confidence['mean_pae'])) {
                $lines[] = '- PAE medio: '.self::fmt((float) $confidence['mean_pae'], 2).' Å';
            }
            $hist = $confidence['plddt_histogram'] ?? null;
            if (is_array($hist)) {
                $parts = [];
                foreach (['very_high' => 'muy alta (>90)', 'high' => 'alta (70-90)', 'medium' => 'media (50-70)', 'low' => 'baja (<50)'] as $key => $label) {
                    if (isset($hist[$key])) {
                        $parts[] = $label.' '.self::fmt((float) $hist[$key], 1).'%';
                    }
                }
                if ($parts !== []) {
                    $lines[] = '- Distribución pLDDT: '.implode(' · ', $parts);
                }
            }

            $perResidue = $confidence['plddt_per_residue'] ?? null;
            if (is_array($perResidue) && count($perResidue) > 0) {
                $lines[] = '- Residuos evaluados: '.count($perResidue);
                $lowRegions = self::findLowConfidenceRegions($perResidue);
                if ($lowRegions !== []) {
                    $lines[] = '- Regiones de baja confianza (pLDDT<70): '.implode(', ', $lowRegions);
                }
            }
            $lines[] = '';
        }

        $bio = $outputs['biological_data'] ?? [];
        if (! empty($bio)) {
            $lines[] = 'PROPIEDADES BIOLÓGICAS';
            if (isset($bio['solubility_score'])) {
                $lines[] = '- Solubilidad: '.self::fmt((float) $bio['solubility_score'], 1).' / 100 ('.($bio['solubility_prediction'] ?? '—').')';
            }
            if (isset($bio['instability_index'])) {
                $lines[] = '- Índice de inestabilidad: '.self::fmt((float) $bio['instability_index'], 1).' ('.($bio['stability_status'] ?? '—').')';
            }
            $tox = $bio['toxicity_alerts'] ?? [];
            $lines[] = '- Alertas toxicidad: '.(empty($tox) ? 'ninguna' : implode(', ', (array) $tox));
            $allerg = $bio['allergenicity_alerts'] ?? [];
            $lines[] = '- Alertas alergenicidad: '.(empty($allerg) ? 'ninguna' : implode(', ', (array) $allerg));

            $ss = $bio['secondary_structure_prediction'] ?? null;
            if (is_array($ss)) {
                $lines[] = '- Estructura secundaria: α-hélice '
                    .self::fmt((float) ($ss['helix_percent'] ?? 0), 0).'% · β-lámina '
                    .self::fmt((float) ($ss['strand_percent'] ?? 0), 0).'% · bucle '
                    .self::fmt((float) ($ss['coil_percent'] ?? 0), 0).'%';
            }
            $lines[] = '';
        }

        if ($accounting !== null && isset($accounting['accounting'])) {
            $a = $accounting['accounting'];
            $lines[] = 'RECURSOS HPC';
            if (isset($a['gpu_hours'])) {
                $lines[] = '- Horas GPU: '.self::fmt((float) $a['gpu_hours'], 4);
            }
            if (isset($a['cpu_hours'])) {
                $lines[] = '- Horas CPU: '.self::fmt((float) $a['cpu_hours'], 4);
            }
            if (isset($a['total_wall_time_seconds'])) {
                $lines[] = '- Tiempo de pared: '.((int) $a['total_wall_time_seconds']).'s';
            }
            $lines[] = '';
        }

        return trim(implode("\n", $lines));
    }

    private static function fmt(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', '');
    }

    private static function plddtQualitativeLabel(float $plddt): string
    {
        return match (true) {
            $plddt >= 90 => 'muy alta',
            $plddt >= 70 => 'alta',
            $plddt >= 50 => 'media',
            default => 'baja',
        };
    }

    /**
     * Compact contiguous low-confidence residue spans (pLDDT<70) into ranges.
     *
     * @param  array<int, float|int>  $perResidue
     * @return list<string>
     */
    private static function findLowConfidenceRegions(array $perResidue): array
    {
        $regions = [];
        $start = null;

        foreach ($perResidue as $idx => $value) {
            $isLow = ((float) $value) < 70;
            if ($isLow && $start === null) {
                $start = $idx + 1;
            }
            if (! $isLow && $start !== null) {
                $regions[] = $start === $idx ? (string) $start : "{$start}-{$idx}";
                $start = null;
            }
        }

        if ($start !== null) {
            $end = count($perResidue);
            $regions[] = $start === $end ? (string) $start : "{$start}-{$end}";
        }

        return array_slice($regions, 0, 10);
    }
}
