<?php

namespace Database\Factories;

use App\Models\Answer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attempt_id' => \App\Models\Attempt::factory(),

            'question_id' => \App\Models\Question::factory(),

            'option_id' => \App\Models\Option::factory(),
        ];
    }
}
