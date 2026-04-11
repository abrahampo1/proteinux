<?php

namespace App\Support\Ai;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Crypt;

class AiSettings
{
    private const SESSION_KEY = 'ai_settings';

    public const PROVIDERS = ['anthropic', 'openai', 'gemini'];

    public function __construct(private readonly Session $session) {}

    public function isConfigured(): bool
    {
        $provider = $this->activeProvider();

        return $provider !== null && $this->apiKeyFor($provider) !== null;
    }

    public function activeProvider(): ?string
    {
        $data = $this->raw();

        $provider = $data['active_provider'] ?? null;

        return in_array($provider, self::PROVIDERS, true) ? $provider : null;
    }

    public function apiKeyFor(string $provider): ?string
    {
        $encrypted = $this->raw()['providers'][$provider]['key_enc'] ?? null;

        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }
    }

    public function modelFor(string $provider): string
    {
        $stored = $this->raw()['providers'][$provider]['model'] ?? null;

        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return (string) config("services.llm.{$provider}.default_model", '');
    }

    public function maskedKeyFor(string $provider): ?string
    {
        $key = $this->apiKeyFor($provider);

        if ($key === null) {
            return null;
        }

        $last4 = substr($key, -4);

        return '•••••••••••••••'.$last4;
    }

    public function hasKeyFor(string $provider): bool
    {
        return $this->apiKeyFor($provider) !== null;
    }

    /**
     * Persist settings. Keys are preserved when the incoming value is null/empty.
     *
     * @param  array{active_provider?: ?string, providers?: array<string, array{api_key?: ?string, model?: ?string}>}  $input
     */
    public function save(array $input): void
    {
        $data = $this->raw();
        $data['providers'] ??= [];

        $lastAddedKey = null;

        foreach (self::PROVIDERS as $provider) {
            $incoming = $input['providers'][$provider] ?? [];

            $providerData = $data['providers'][$provider] ?? [];

            $newKey = $incoming['api_key'] ?? null;
            if (is_string($newKey) && trim($newKey) !== '') {
                $providerData['key_enc'] = Crypt::encryptString(trim($newKey));
                $lastAddedKey = $provider;
            }

            $newModel = $incoming['model'] ?? null;
            if (is_string($newModel) && trim($newModel) !== '') {
                $providerData['model'] = trim($newModel);
            }

            $data['providers'][$provider] = $providerData;
        }

        if (isset($input['active_provider']) && in_array($input['active_provider'], self::PROVIDERS, true)) {
            $data['active_provider'] = $input['active_provider'];
        }

        $currentActive = $data['active_provider'] ?? null;
        $activeIsUsable = is_string($currentActive)
            && isset($data['providers'][$currentActive]['key_enc'])
            && $data['providers'][$currentActive]['key_enc'] !== '';

        if (! $activeIsUsable) {
            $data['active_provider'] = $lastAddedKey ?? $this->firstProviderWithKey($data);
        }

        $this->session->put(self::SESSION_KEY, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function firstProviderWithKey(array $data): ?string
    {
        foreach (self::PROVIDERS as $provider) {
            $enc = $data['providers'][$provider]['key_enc'] ?? null;
            if (is_string($enc) && $enc !== '') {
                return $provider;
            }
        }

        return null;
    }

    public function clear(string $provider): void
    {
        $data = $this->raw();

        if (isset($data['providers'][$provider])) {
            unset($data['providers'][$provider]);
        }

        if (($data['active_provider'] ?? null) === $provider) {
            $data['active_provider'] = null;
        }

        $this->session->put(self::SESSION_KEY, $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function raw(): array
    {
        $data = $this->session->get(self::SESSION_KEY, []);

        return is_array($data) ? $data : [];
    }
}
