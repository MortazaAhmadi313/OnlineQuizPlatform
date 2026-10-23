<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Category;
use App\Models\Leaderboard;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardBestScoreTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::factory()->create();

        $this->student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->quiz = Quiz::factory()->create([
            'category_id' => $category->id,
            'created_by' => $admin->id,
            'status' => 'published',
        ]);
    }

    private function createAttempt(float $score): Attempt
    {
        return Attempt::factory()->create([
            'user_id' => $this->student->id,
            'quiz_id' => $this->quiz->id,
            'score' => $score,
            'status' => 'completed',
        ]);
    }

    public function test_higher_score_replaces_previous_leaderboard_score(): void
    {
        $firstAttempt = $this->createAttempt(60);

        Leaderboard::updateBestScore($firstAttempt);

        $secondAttempt = $this->createAttempt(90);

        Leaderboard::updateBestScore($secondAttempt);

        $this->assertDatabaseHas('leaderboards', [
            'quiz_id' => $this->quiz->id,
            'user_id' => $this->student->id,
            'attempt_id' => $secondAttempt->id,
            'score' => 90,
        ]);

        $this->assertDatabaseCount('leaderboards', 1);
    }

    public function test_lower_score_does_not_replace_previous_best_score(): void
    {
        $firstAttempt = $this->createAttempt(90);

        Leaderboard::updateBestScore($firstAttempt);

        $secondAttempt = $this->createAttempt(50);

        Leaderboard::updateBestScore($secondAttempt);

        $this->assertDatabaseHas('leaderboards', [
            'quiz_id' => $this->quiz->id,
            'user_id' => $this->student->id,
            'attempt_id' => $firstAttempt->id,
            'score' => 90,
        ]);

        $this->assertDatabaseCount('leaderboards', 1);
    }

    public function test_ranks_are_recalculated_by_score(): void
    {
        $firstStudent = User::factory()->create([
            'role' => 'student',
        ]);

        $firstAttempt = Attempt::factory()->create([
            'user_id' => $firstStudent->id,
            'quiz_id' => $this->quiz->id,
            'score' => 70,
            'status' => 'completed',
        ]);

        Leaderboard::updateBestScore($firstAttempt);

        $secondAttempt = $this->createAttempt(95);

        Leaderboard::updateBestScore($secondAttempt);

        $this->assertDatabaseHas('leaderboards', [
            'quiz_id' => $this->quiz->id,
            'user_id' => $this->student->id,
            'score' => 95,
            'rank' => 1,
        ]);

        $this->assertDatabaseHas('leaderboards', [
            'quiz_id' => $this->quiz->id,
            'user_id' => $firstStudent->id,
            'score' => 70,
            'rank' => 2,
        ]);
    }
}
