<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Leaderboard;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create categories
        Category::insert([
            [
                'name' => 'Programming',
                'description' => 'Programming quizzes',
            ],
            [
                'name' => 'Mathematics',
                'description' => 'Mathematics quizzes',
            ],
            [
                'name' => 'Database',
                'description' => 'Database quizzes',
            ],
            [
                'name' => 'Web Development',
                'description' => 'Web development quizzes',
            ],
            [
                'name' => 'Networking',
                'description' => 'Networking quizzes',
            ],
            [
                'name' => 'Computer Science',
                'description' => 'Computer science quizzes',
            ],
            [
                'name' => 'English',
                'description' => 'English quizzes',
            ],
            [
                'name' => 'History',
                'description' => 'History quizzes',
            ],
        ]);

        // Create admin
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Create students
        $students = User::factory(10)->create();

        // Create quizzes
        $quizzes = Quiz::factory(10)->create([
            'created_by' => $admin->id,
        ]);

        // Create questions and options for each quiz
        foreach ($quizzes as $quiz) {
            $questions = Question::factory(5)->create([
                'quiz_id' => $quiz->id,
            ]);

            foreach ($questions as $question) {
                Option::factory()->create([
                    'question_id' => $question->id,
                    'is_correct' => true,
                ]);

                Option::factory(3)->create([
                    'question_id' => $question->id,
                    'is_correct' => false,
                ]);
            }
        }

        // Create attempts and answers for students
        foreach ($students->take(5) as $student) {
            foreach ($quizzes->take(3) as $quiz) {
                $attempt = Attempt::factory()->create([
                    'user_id' => $student->id,
                    'quiz_id' => $quiz->id,
                ]);

                $questions = Question::where('quiz_id', $quiz->id)->get();

                foreach ($questions as $question) {
                    $option = $question->options()->inRandomOrder()->first();

                    Answer::factory()->create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'option_id' => $option->id,
                    ]);
                }
            }
        }

        // Create leaderboards
        foreach ($quizzes->take(3) as $quiz) {
            $attempts = Attempt::where('quiz_id', $quiz->id)
                ->orderByDesc('score')
                ->get();

            $rank = 1;

            foreach ($attempts as $attempt) {
                Leaderboard::factory()->create([
                    'quiz_id' => $quiz->id,
                    'user_id' => $attempt->user_id,
                    'attempt_id' => $attempt->id,
                    'score' => $attempt->score,
                    'rank' => $rank,
                ]);

                $rank++;
            }
        }
    }
}
