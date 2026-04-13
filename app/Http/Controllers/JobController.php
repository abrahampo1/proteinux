<?php

namespace App\Http\Controllers;

use App\Exceptions\CesgaApiException;
use App\Http\Requests\JobSubmitRequest;
use App\Models\Forum\ForumThread;
use App\Services\CesgaApiService;
use App\Services\JobLibrary;
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

    public function store(JobSubmitRequest $request, CesgaApiService $api, JobLibrary $library): RedirectResponse
    {
        $fasta = $request->validated('fasta_sequence');
        $force = $request->boolean('force');

        if (! $force) {
            $existing = $library->findByFasta($fasta);
            if ($existing !== null) {
                // Verificamos que el job siga vivo en CESGA. Si no, limpiamos
                // la entrada stale y seguimos con el envío normal.
                try {
                    $api->getJobStatus($existing->job_id);

                    return redirect()
                        ->route('jobs.show', $existing->job_id)
                        ->with('info', 'Esta proteína ya estaba en la biblioteca. Pulsa «ejecutar de nuevo» si quieres forzar una nueva predicción.');
                } catch (CesgaApiException) {
                    $library->deleteByJobId($existing->job_id);
                }
            }
        }

        try {
            $result = $api->submitJob(
                fastaSequence: $fasta,
                fastaFilename: $request->validated('fasta_filename'),
                gpus: $request->validated('gpus', 1),
                cpus: $request->validated('cpus', 8),
                memoryGb: (float) $request->validated('memory_gb', 32.0),
            );

            $library->registerSubmission(
                fasta: $fasta,
                jobId: $result['job_id'],
                fastaFilename: $request->validated('fasta_filename'),
            );

            return redirect()->route('jobs.show', $result['job_id']);
        } catch (CesgaApiException $e) {
            return back()->withInput()->withErrors(['api' => $e->getMessage()]);
        }
    }

    public function show(string $jobId, CesgaApiService $api, JobLibrary $library): View
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
                $library->enrichFromOutputs($jobId, $outputs);
            } catch (CesgaApiException) {
                // outputs may not be ready yet
            }
        }

        $libraryEntry = $library->findByJobId($jobId);

        $threads = collect();
        if ($libraryEntry) {
            $threads = ForumThread::with(['user', 'remoteUser'])
                ->forPredictedJob($libraryEntry->id)
                ->orderByDesc('last_activity_at')
                ->limit(5)
                ->get();
        }

        return view('jobs.show', compact('jobId', 'status', 'outputs', 'accounting', 'libraryEntry', 'threads'));
    }
}
