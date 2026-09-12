<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => \App\Models\Category::inRandomOrder()->first()->id,

            'created_by' => \App\Models\User::where('role', 'admin')->first()->id,

            'title' => fake()->sentence(4),

            'description' => fake()->paragraph(),

            'duration' => fake()->randomElement([10, 15, 20, 30, 45]),

            'status' => 'published',
        ];
    }
}
