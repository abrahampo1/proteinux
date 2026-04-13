<?php

namespace Database\Factories\Scientific;

use App\Models\Scientific\ScientificDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScientificDocument>
 */
class ScientificDocumentFactory extends Factory
{
    protected $model = ScientificDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['paper', 'note', 'protocol', 'dataset']),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Document with a DOI.
     */
    public function withDoi(): static
    {
        return $this->state(fn (array $attributes) => [
            'doi' => '10.'.fake()->numerify('####').'/'.fake()->lexify('??????'),
        ]);
    }

    /**
     * Document with an external URL.
     */
    public function withUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => fake()->url(),
        ]);
    }

    /**
     * Document marked as shared for federation.
     */
    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shared' => true,
        ]);
    }

    /**
     * Paper type document.
     */
    public function paper(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'paper',
        ]);
    }

    /**
     * Note type document.
     */
    public function note(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'note',
        ]);
    }

    /**
     * Protocol type document.
     */
    public function protocol(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'protocol',
        ]);
    }

    /**
     * Dataset type document.
     */
    public function dataset(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'dataset',
        ]);
    }
}
