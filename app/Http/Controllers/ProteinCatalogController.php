<?php

namespace App\Http\Controllers;

use App\Exceptions\CesgaApiException;
use App\Models\Forum\ForumThread;
use App\Services\CesgaApiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProteinCatalogController extends Controller
{
    public function index(Request $request, CesgaApiService $api): View
    {
        try {
            $proteins = $api->listProteins(
                category: $request->query('category'),
                search: $request->query('search'),
            );
        } catch (CesgaApiException) {
            $proteins = [];
        }

        $categories = ['enzyme', 'transport', 'signaling', 'immune', 'hormone', 'reporter', 'structural', 'oncology', 'dna-replication'];

        return view('proteins.index', [
            'proteins' => $proteins,
            'categories' => $categories,
            'currentCategory' => $request->query('category'),
            'currentSearch' => $request->query('search'),
        ]);
    }

    public function show(string $proteinId, CesgaApiService $api): View
    {
        try {
            $protein = $api->getProtein($proteinId);
        } catch (CesgaApiException $e) {
            abort(404, 'Protein not found');
        }

        $threads = ForumThread::with(['user', 'remoteUser'])
            ->where('protein_reference', $proteinId)
            ->orderByDesc('last_activity_at')
            ->limit(5)
            ->get();

        $threadCount = ForumThread::where('protein_reference', $proteinId)->count();

        return view('proteins.show', compact('protein', 'threads', 'threadCount'));
    }
}
