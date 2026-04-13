<?php

namespace Database\Factories\Forum;

use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumPost>
 */
class ForumPostFactory extends Factory
{
    protected $model = ForumPost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'body' => fake()->paragraph(),
            'forum_thread_id' => ForumThread::factory(),
            'user_id' => User::factory(),
        ];
    }
}
