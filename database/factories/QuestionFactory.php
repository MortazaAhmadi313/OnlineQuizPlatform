<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
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

            'question_text' => fake()->sentence(10),

            'points' => fake()->randomElement([1, 2, 3, 5]),

            'difficulty' => fake()->randomElement([
                'easy',
                'medium',
                'hard',
            ]),
        ];
    }
}
