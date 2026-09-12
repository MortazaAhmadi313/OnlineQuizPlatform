<?php

namespace Database\Factories;

use App\Models\Attempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attempt>
 */
class AttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),

            'quiz_id' => \App\Models\Quiz::factory(),

            'score' => fake()->randomFloat(2, 0, 100),

            'status' => 'completed',

            'started_at' => now()->subMinutes(30),

            'completed_at' => now(),
        ];
    }
}
