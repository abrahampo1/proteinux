<?php

namespace App\Services;

use App\Exceptions\CesgaApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CesgaApiService
{
    private function client(): PendingRequest
    {
        return Http::baseUrl(config('services.cesga.base_url'))
            ->timeout(config('services.cesga.timeout'))
            ->acceptJson();
    }

    /**
     * @throws CesgaApiException
     */
    private function get(string $endpoint, array $query = []): array
    {
        try {
            $response = $this->client()->get($endpoint, $query);
        } catch (\Exception $e) {
            Log::error("CESGA API connection failed: {$e->getMessage()}");
            throw CesgaApiException::connectionFailed($e->getMessage());
        }

        if ($response->failed()) {
            throw CesgaApiException::fromResponse($response->status(), $response->json() ?? []);
        }

        return $response->json();
    }

    /**
     * @throws CesgaApiException
     */
    private function post(string $endpoint, array $data): array
    {
        try {
            $response = $this->client()->post($endpoint, $data);
        } catch (\Exception $e) {
            Log::error("CESGA API connection failed: {$e->getMessage()}");
            throw CesgaApiException::connectionFailed($e->getMessage());
        }

        if ($response->failed()) {
            throw CesgaApiException::fromResponse($response->status(), $response->json() ?? []);
        }

        return $response->json();
    }

    public function healthCheck(): array
    {
        return $this->get('/health');
    }

    public function submitJob(
        string $fastaSequence,
        string $fastaFilename,
        int $gpus = 1,
        int $cpus = 8,
        float $memoryGb = 32.0,
        int $maxRuntimeSeconds = 3600,
    ): array {
        return $this->post('/jobs/submit', [
            'fasta_sequence' => $fastaSequence,
            'fasta_filename' => $fastaFilename,
            'gpus' => $gpus,
            'cpus' => $cpus,
            'memory_gb' => $memoryGb,
            'max_runtime_seconds' => $maxRuntimeSeconds,
        ]);
    }

    public function getJobStatus(string $jobId): array
    {
        return $this->get("/jobs/{$jobId}/status");
    }

    public function getJobOutputs(string $jobId): array
    {
        return $this->get("/jobs/{$jobId}/outputs");
    }

    public function getJobAccounting(string $jobId): array
    {
        return $this->get("/jobs/{$jobId}/accounting");
    }

    public function listJobs(int $skip = 0, int $limit = 100): array
    {
        return $this->get('/jobs/', ['skip' => $skip, 'limit' => $limit]);
    }

    public function listProteins(?string $category = null, ?string $search = null, ?int $minLength = null, ?int $maxLength = null): array
    {
        return $this->get('/proteins/', array_filter([
            'category' => $category,
            'search' => $search,
            'min_length' => $minLength,
            'max_length' => $maxLength,
        ]));
    }

    public function getProtein(string $proteinId): array
    {
        return $this->get("/proteins/{$proteinId}");
    }

    public function getSampleSequences(): array
    {
        return Cache::remember('cesga_samples', 600, fn () => $this->get('/proteins/samples'));
    }

    public function getProteinStats(): array
    {
        return Cache::remember('cesga_protein_stats', 600, fn () => $this->get('/proteins/stats'));
    }
}
