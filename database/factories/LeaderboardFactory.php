<?php

namespace Database\Factories;

use App\Models\Leaderboard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Leaderboard>
 */
class LeaderboardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => \App\Models\Quiz::factory(),

            'user_id' => \App\Models\User::factory(),

            'attempt_id' => \App\Models\Attempt::factory(),

            'score' => fake()->randomFloat(2, 0, 100),

            'rank' => fake()->numberBetween(1, 10),
        ];
    }
}
