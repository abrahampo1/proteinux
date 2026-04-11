<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAiSettingsRequest;
use App\Support\Ai\AiSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiSettingsController extends Controller
{
    public function edit(AiSettings $settings): View
    {
        return view('settings.ai', [
            'settings' => $settings,
            'providers' => config('services.llm'),
        ]);
    }

    public function update(UpdateAiSettingsRequest $request, AiSettings $settings): RedirectResponse
    {
        $settings->save($request->validated());

        return redirect()
            ->route('settings.ai')
            ->with('success', 'Ajustes de IA guardados.');
    }

    public function destroy(string $provider, AiSettings $settings): RedirectResponse
    {
        if (! in_array($provider, AiSettings::PROVIDERS, true)) {
            abort(404);
        }

        $settings->clear($provider);

        return redirect()
            ->route('settings.ai')
            ->with('success', "Key de {$provider} eliminada.");
    }
}
