<?php

namespace App\Http\Controllers;

use App\Exceptions\CesgaApiException;
use App\Models\PredictedJob;
use App\Services\CesgaApiService;
use App\Services\JobLibrary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobLibraryController extends Controller
{
    /**
     * Máximo de rows pendientes que consultamos contra CESGA en un solo
     * render de /biblioteca. Suficiente para la mayoría de casos y evita
     * que una página con decenas de pendientes queme la API.
     */
    private const LAZY_ENRICH_BUDGET = 10;

    public function index(Request $request, JobLibrary $library, CesgaApiService $api): View
    {
        $search = trim((string) $request->query('q', ''));
        $source = $request->query('source', 'all');

        /** @var LengthAwarePaginator<int, PredictedJob> $jobs */
        $jobs = PredictedJob::query()
            ->when($source === 'local', fn ($q) => $q->where('is_remote', false))
            ->when($source === 'federated', fn ($q) => $q->where('is_remote', true))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like) {
                    $inner->where('protein_name', 'like', $like)
                        ->orWhere('organism', 'like', $like)
                        ->orWhere('uniprot_id', 'like', $like)
                        ->orWhere('pdb_id', 'like', $like);
                });
            })
            ->orderByRaw('COALESCE(completed_at, created_at) DESC')
            ->paginate(24)
            ->withQueryString();

        // Lazy enrichment: para las rows que no tienen pLDDT (sin metadata
        // todavía) consultamos CESGA. Si el job ya está COMPLETED en el
        // backend, rellenamos nombre, organismo, score, etc.
        $budget = self::LAZY_ENRICH_BUDGET;
        foreach ($jobs->getCollection() as $key => $entry) {
            if ($entry->plddt_mean !== null) {
                continue;
            }
            if ($budget <= 0) {
                break;
            }
            $budget--;
            try {
                $status = $api->getJobStatus($entry->job_id);
                if (($status['status'] ?? null) === 'COMPLETED') {
                    $outputs = $api->getJobOutputs($entry->job_id);
                    $fresh = $library->enrichFromOutputs($entry->job_id, $outputs);
                    if ($fresh !== null) {
                        $jobs->getCollection()->put($key, $fresh);
                    }
                }
            } catch (CesgaApiException) {
                // Job aún en cola o stale — seguimos mostrándolo como pendiente.
            }
        }

        $total = PredictedJob::count();
        $completed = PredictedJob::whereNotNull('completed_at')->count();

        return view('library.index', compact('jobs', 'search', 'source', 'total', 'completed'));
    }

    public function share(PredictedJob $predictedJob, JobLibrary $library): RedirectResponse
    {
        $library->shareEntry($predictedJob);

        return back()->with('success', 'Prediccion compartida con las instancias federadas.');
    }

    public function rerun(PredictedJob $predictedJob, CesgaApiService $api): RedirectResponse
    {
        try {
            $result = $api->submitJob(
                fastaSequence: $predictedJob->fasta_sequence,
                fastaFilename: $predictedJob->fasta_filename ?: 'rerun.fasta',
            );
        } catch (CesgaApiException $e) {
            return back()->withErrors(['api' => $e->getMessage()]);
        }

        // La biblioteca pasa a apuntar al nuevo job. Conservamos la metadata
        // del anterior para que la card siga mostrando algo coherente
        // mientras la nueva predicción corre.
        $predictedJob->update([
            'job_id' => $result['job_id'],
            'completed_at' => null,
            'plddt_mean' => null,
        ]);

        return redirect()
            ->route('jobs.show', $result['job_id'])
            ->with('success', 'Re-ejecución lanzada.');
    }
}
