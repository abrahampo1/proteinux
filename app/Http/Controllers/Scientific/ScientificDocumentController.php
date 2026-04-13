<?php

namespace App\Http\Controllers\Scientific;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScientificDocumentRequest;
use App\Models\PredictedJob;
use App\Models\Scientific\ScientificDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScientificDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $query = ScientificDocument::with(['proteins'])
            ->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('doi', 'like', "%{$search}%");
            });
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $documents = $query->paginate(20)->withQueryString();

        return view('scientific.index', [
            'documents' => $documents,
            'currentSearch' => $search,
            'currentType' => $type,
        ]);
    }

    public function create(): View
    {
        $predictedJobs = PredictedJob::whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get();

        return view('scientific.create', [
            'predictedJobs' => $predictedJobs,
        ]);
    }

    public function store(StoreScientificDocumentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $document = ScientificDocument::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'url' => $validated['url'] ?? null,
            'doi' => $validated['doi'] ?? null,
            'user_id' => $request->user()->id,
            'is_shared' => $validated['is_shared'] ?? false,
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $directory = "scientific/{$document->id}";
            $path = $file->storeAs($directory, $file->getClientOriginalName(), 'private');

            $document->update([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }

        if (! empty($validated['protein_ids'])) {
            $document->proteins()->sync($validated['protein_ids']);
        }

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Documento subido correctamente.');
    }

    public function show(ScientificDocument $document): View
    {
        $document->load(['proteins']);

        return view('scientific.show', [
            'document' => $document,
        ]);
    }

    public function download(ScientificDocument $document): StreamedResponse|RedirectResponse
    {
        if ($document->hasFile()) {
            $disk = Storage::disk('private');

            if (! $disk->exists($document->file_path)) {
                abort(404, 'Archivo no encontrado.');
            }

            return $disk->download($document->file_path, $document->file_name);
        }

        if ($document->url) {
            return redirect()->away($document->url);
        }

        abort(404, 'No hay archivo ni enlace disponible.');
    }
}
