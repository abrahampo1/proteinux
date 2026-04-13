<?php

namespace Database\Factories\Federation;

use App\Models\Federation\FederationInstance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FederationInstance>
 */
class FederationInstanceFactory extends Factory
{
    protected $model = FederationInstance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $details = openssl_pkey_get_details($resource);

        return [
            'domain' => fake()->unique()->domainName(),
            'name' => fake()->company().' Proteinux',
            'description' => fake()->sentence(),
            'public_key' => $details['key'],
            'status' => 'active',
            'last_seen_at' => now(),
            'metadata' => [],
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
