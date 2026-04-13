<?php

namespace App\Models\Federation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class FederationKeypair extends Model
{
    protected $fillable = [
        'public_key',
        'private_key_encrypted',
    ];

    /**
     * Get the existing keypair or generate a new RSA 4096-bit keypair.
     */
    public static function getOrCreate(): self
    {
        $existing = static::query()->first();

        if ($existing) {
            return $existing;
        }

        $config = [
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $resource = openssl_pkey_new($config);
        openssl_pkey_export($resource, $privateKeyPem);

        $details = openssl_pkey_get_details($resource);
        $publicKeyPem = $details['key'];

        return static::create([
            'public_key' => $publicKeyPem,
            'private_key_encrypted' => Crypt::encryptString($privateKeyPem),
        ]);
    }

    /**
     * Decrypt and return the private key.
     */
    public function decryptedPrivateKey(): string
    {
        return Crypt::decryptString($this->private_key_encrypted);
    }
}
