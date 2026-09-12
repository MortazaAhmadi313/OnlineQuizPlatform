<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Programming',
                'Mathematics',
                'Database',
                'Web Development',
                'Networking',
                'Computer Science',
                'English',
                'History',
            ]),

            'description' => fake()->sentence(8),
        ];
    }
}
