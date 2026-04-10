<?php

namespace App\Http\Controllers;

use App\Exceptions\CesgaApiException;
use App\Http\Requests\JobSubmitRequest;
use App\Services\CesgaApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function create(Request $request, CesgaApiService $api): View
    {
        try {
            $samples = $api->getSampleSequences();
        } catch (CesgaApiException) {
            $samples = [];
        }

        return view('jobs.create', [
            'samples' => $samples,
            'prefillFasta' => $request->query('fasta'),
            'prefillFilename' => $request->query('filename'),
        ]);
    }

    public function store(JobSubmitRequest $request, CesgaApiService $api): RedirectResponse
    {
        try {
            $result = $api->submitJob(
                fastaSequence: $request->validated('fasta_sequence'),
                fastaFilename: $request->validated('fasta_filename'),
                gpus: $request->validated('gpus', 1),
                cpus: $request->validated('cpus', 8),
                memoryGb: (float) $request->validated('memory_gb', 32.0),
            );

            return redirect()->route('jobs.show', $result['job_id']);
        } catch (CesgaApiException $e) {
            return back()->withInput()->withErrors(['api' => $e->getMessage()]);
        }
    }

    public function show(string $jobId, CesgaApiService $api): View
    {
        try {
            $status = $api->getJobStatus($jobId);
        } catch (CesgaApiException $e) {
            abort(404, 'Job not found');
        }

        $outputs = null;
        $accounting = null;

        if ($status['status'] === 'COMPLETED') {
            try {
                $outputs = $api->getJobOutputs($jobId);
                $accounting = $api->getJobAccounting($jobId);
            } catch (CesgaApiException) {
                // outputs may not be ready yet
            }
        }

        return view('jobs.show', compact('jobId', 'status', 'outputs', 'accounting'));
    }
}
