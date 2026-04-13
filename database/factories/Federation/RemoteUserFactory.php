<?php

namespace Database\Factories\Federation;

use App\Models\Federation\FederationInstance;
use App\Models\Federation\RemoteUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RemoteUser>
 */
class RemoteUserFactory extends Factory
{
    protected $model = RemoteUser::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'domain' => fake()->domainName(),
            'display_name' => fake()->name(),
            'institution' => fake()->company(),
            'federation_instance_id' => FederationInstance::factory(),
            'avatar_url' => fake()->optional()->imageUrl(128, 128),
        ];
    }
}
