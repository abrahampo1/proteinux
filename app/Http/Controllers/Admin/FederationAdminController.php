<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Federation\FederationInstance;
use App\Services\Federation\FederationService;
use App\Services\Federation\PeerDiscoveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FederationAdminController extends Controller
{
    public function __construct(
        private FederationService $federationService,
        private PeerDiscoveryService $peerDiscoveryService,
    ) {}

    /**
     * Show the federation admin panel.
     */
    public function index(): View
    {
        $instances = FederationInstance::orderByDesc('updated_at')->get();
        $localInfo = $this->federationService->localInstanceInfo();

        return view('admin.federation', [
            'instances' => $instances,
            'localInfo' => $localInfo,
            'federationEnabled' => $this->federationService->isEnabled(),
        ]);
    }

    /**
     * Connect to a new remote federation instance.
     */
    public function connect(Request $request): RedirectResponse
    {
        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        try {
            $this->peerDiscoveryService->requestPeering($request->input('domain'));

            return redirect()->route('admin.federation.index')
                ->with('success', 'Solicitud de federación enviada.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.federation.index')
                ->withErrors(['api' => "Error al conectar: {$e->getMessage()}"]);
        }
    }

    /**
     * Accept a pending peering request.
     */
    public function accept(FederationInstance $instance): RedirectResponse
    {
        $this->peerDiscoveryService->acceptPeering($instance);

        return redirect()->route('admin.federation.index')
            ->with('success', "Instancia {$instance->domain} aceptada.");
    }

    /**
     * Reject a pending peering request.
     */
    public function reject(FederationInstance $instance): RedirectResponse
    {
        $this->peerDiscoveryService->rejectPeering($instance);

        return redirect()->route('admin.federation.index')
            ->with('success', "Instancia {$instance->domain} rechazada.");
    }

    /**
     * Remove a federation instance.
     */
    public function destroy(FederationInstance $instance): RedirectResponse
    {
        $domain = $instance->domain;
        $instance->delete();

        return redirect()->route('admin.federation.index')
            ->with('success', "Instancia {$domain} eliminada.");
    }
}
