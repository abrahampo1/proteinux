<?php

namespace App\Services\Federation;

use Illuminate\Http\Request;

class HttpSignatureService
{
    /**
     * Sign an outgoing request and return the required headers.
     *
     * @return array{Signature: string, Date: string, Digest: string, Host: string}
     */
    public function sign(string $url, string $body, string $privateKey): array
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';
        $path = $parsed['path'] ?? '/';
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $digest = 'SHA-256='.base64_encode(hash('sha256', $body, true));

        $requestTarget = 'post '.strtolower($path);

        $signingString = $this->buildSigningString([
            '(request-target)' => $requestTarget,
            'host' => $host,
            'date' => $date,
            'digest' => $digest,
        ]);

        $localDomain = config('services.federation.domain');
        $keyId = $localDomain.'#main-key';

        openssl_sign($signingString, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureBase64 = base64_encode($signature);

        $signatureHeader = sprintf(
            'keyId="%s",algorithm="rsa-sha256",headers="(request-target) host date digest",signature="%s"',
            $keyId,
            $signatureBase64,
        );

        return [
            'Signature' => $signatureHeader,
            'Date' => $date,
            'Digest' => $digest,
            'Host' => $host,
        ];
    }

    /**
     * Verify an incoming request's HTTP Signature.
     */
    public function verify(Request $request, string $publicKey): bool
    {
        $signatureHeader = $request->header('Signature');

        if (! $signatureHeader) {
            return false;
        }

        // Verify the Digest header matches the body
        $expectedDigest = 'SHA-256='.base64_encode(hash('sha256', $request->getContent(), true));
        if ($request->header('Digest') !== $expectedDigest) {
            return false;
        }

        $params = $this->parseSignatureHeader($signatureHeader);

        if (! isset($params['signature'], $params['headers'])) {
            return false;
        }

        $headerNames = explode(' ', $params['headers']);
        $components = [];

        foreach ($headerNames as $headerName) {
            if ($headerName === '(request-target)') {
                $method = strtolower($request->method());
                $path = $request->getRequestUri();
                $components['(request-target)'] = $method.' '.$path;
            } else {
                $components[$headerName] = $request->header($headerName) ?? '';
            }
        }

        $signingString = $this->buildSigningString($components);
        $decodedSignature = base64_decode($params['signature']);

        return openssl_verify($signingString, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * Build the signing string from header components.
     *
     * @param  array<string, string>  $components
     */
    private function buildSigningString(array $components): string
    {
        $lines = [];

        foreach ($components as $name => $value) {
            $lines[] = strtolower($name).': '.$value;
        }

        return implode("\n", $lines);
    }

    /**
     * Parse the Signature header into its components.
     *
     * @return array<string, string>
     */
    private function parseSignatureHeader(string $header): array
    {
        $params = [];

        preg_match_all('/(\w+)="([^"]+)"/', $header, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $params[$match[1]] = $match[2];
        }

        return $params;
    }
}
