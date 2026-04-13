<?php

namespace App\Http\Middleware;

use App\Models\Federation\FederationInstance;
use App\Services\Federation\HttpSignatureService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyFederationSignature
{
    public function __construct(
        private HttpSignatureService $signatureService,
    ) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signatureHeader = $request->header('Signature');

        if (! $signatureHeader) {
            abort(401, 'Missing Signature header.');
        }

        // Verify the Digest header matches the request body
        $expectedDigest = 'SHA-256='.base64_encode(hash('sha256', $request->getContent(), true));

        if ($request->header('Digest') !== $expectedDigest) {
            abort(401, 'Invalid Digest header.');
        }

        // Extract keyId domain from the Signature header
        $domain = $this->extractDomainFromKeyId($signatureHeader);

        if (! $domain) {
            abort(401, 'Unable to extract domain from keyId.');
        }

        // Look up the remote instance
        $instance = FederationInstance::where('domain', $domain)->first();

        if (! $instance || ! $instance->public_key) {
            Log::warning("Federation signature verification failed: unknown domain {$domain}");
            abort(401, 'Unknown federation instance.');
        }

        // Verify the signature using the remote instance's public key
        if (! $this->signatureService->verify($request, $instance->public_key)) {
            Log::warning("Federation signature verification failed for domain {$domain}");
            abort(401, 'Invalid federation signature.');
        }

        // Store the verified instance on the request for downstream use
        $request->attributes->set('federation_instance', $instance);

        return $next($request);
    }

    /**
     * Extract the domain from the keyId parameter in the Signature header.
     */
    private function extractDomainFromKeyId(string $signatureHeader): ?string
    {
        if (preg_match('/keyId="([^"]+)"/', $signatureHeader, $matches)) {
            $keyId = $matches[1];

            // keyId format: "domain#main-key"
            $parts = explode('#', $keyId);

            return $parts[0] ?? null;
        }

        return null;
    }
}
