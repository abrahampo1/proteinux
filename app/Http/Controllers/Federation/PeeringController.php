<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\Federation\FederationInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeeringController extends Controller
{
    /**
     * Handle an incoming peering request from a remote instance.
     */
    public function request(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'instance' => 'required|array',
            'instance.domain' => 'required|string',
            'instance.name' => 'required|string',
        ]);

        $remoteInstance = $request->input('instance');

        FederationInstance::updateOrCreate(
            ['domain' => $remoteInstance['domain']],
            [
                'name' => $remoteInstance['name'],
                'description' => $remoteInstance['description'] ?? null,
                'public_key' => $remoteInstance['public_key'] ?? null,
                'status' => 'pending',
                'metadata' => $remoteInstance,
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['status' => 'pending', 'message' => 'Peering request received.']);
    }

    /**
     * Handle a peering acceptance from a remote instance.
     */
    public function accept(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'instance' => 'required|array',
            'instance.domain' => 'required|string',
        ]);

        $remoteDomain = $request->input('instance.domain');

        $instance = FederationInstance::where('domain', $remoteDomain)->first();

        if ($instance) {
            $instance->update([
                'status' => 'active',
                'public_key' => $request->input('instance.public_key', $instance->public_key),
                'last_seen_at' => now(),
            ]);
        }

        return response()->json(['status' => 'active', 'message' => 'Peering accepted.']);
    }

    /**
     * Handle a peering rejection from a remote instance.
     */
    public function reject(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'instance' => 'required|array',
            'instance.domain' => 'required|string',
        ]);

        $remoteDomain = $request->input('instance.domain');

        $instance = FederationInstance::where('domain', $remoteDomain)->first();

        if ($instance) {
            $instance->update(['status' => 'rejected']);
        }

        return response()->json(['status' => 'rejected', 'message' => 'Peering rejected.']);
    }
}
