<?php

namespace App\Http\Controllers;

use App\Exceptions\CesgaApiException;
use App\Models\PredictedJob;
use App\Services\CesgaApiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobLibraryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        /** @var LengthAwarePaginator<int, PredictedJob> $jobs */
        $jobs = PredictedJob::query()
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

        $total = PredictedJob::count();
        $completed = PredictedJob::whereNotNull('completed_at')->count();

        return view('library.index', compact('jobs', 'search', 'total', 'completed'));
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
