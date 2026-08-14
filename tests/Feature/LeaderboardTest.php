<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_leaderboard_with_results()
    {
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'Data Structures',
            'userid' => $teacher->id,
            'start_datetime' => now(),
            'duration' => 60,
        ]);

        Question::create([
            'quiz_id' => $quiz->id,
            'text' => 'Sample Q1',
            'option1' => 'A',
            'option2' => 'B',
            'option3' => 'C',
            'option4' => 'D',
            'right_option' => 1,
            'duration' => 30,
        ]);

        Result::create([
            'quiz_id' => $quiz->id,
            'student_id' => '2107001',
            'score' => 1,
        ]);

        Result::create([
            'quiz_id' => $quiz->id,
            'student_id' => '2107002',
            'score' => 0,
        ]);

        $response = $this->actingAs($teacher)->get(route('quiz.leaderboard', $quiz->id));

        $response->assertStatus(200);
        $response->assertSee('Data Structures');
        $response->assertSee('2107001');
        $response->assertSee('2107002');
        $response->assertSee('Leaderboard');
    }

    public function test_teacher_can_view_score_distribution()
    {
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'Algorithms',
            'userid' => $teacher->id,
            'start_datetime' => now(),
            'duration' => 60,
        ]);

        Question::create([
            'quiz_id' => $quiz->id,
            'text' => 'Sample Q',
            'option1' => 'A',
            'option2' => 'B',
            'option3' => 'C',
            'option4' => 'D',
            'right_option' => 1,
            'duration' => 30,
        ]);

        Result::create([
            'quiz_id' => $quiz->id,
            'student_id' => 'STU100',
            'score' => 1,
        ]);

        $response = $this->actingAs($teacher)->get(route('quiz.performance', $quiz->id));

        $response->assertStatus(200);
        $response->assertSee('Score Percentage Distribution');
        $response->assertSee('Performance Brackets');
    }

    public function test_teacher_can_export_pdf()
    {
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'Operating Systems',
            'userid' => $teacher->id,
            'start_datetime' => now(),
            'duration' => 60,
        ]);

        Result::create([
            'quiz_id' => $quiz->id,
            'student_id' => '2107099',
            'score' => 5,
        ]);

        $response = $this->actingAs($teacher)->get('/leaderboard/export/' . $quiz->id);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_teacher_can_clear_leaderboard()
    {
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'Database Systems',
            'userid' => $teacher->id,
            'start_datetime' => now(),
            'duration' => 60,
        ]);

        Result::create([
            'quiz_id' => $quiz->id,
            'student_id' => '2107050',
            'score' => 10,
        ]);

        $response = $this->actingAs($teacher)->delete(route('leaderboard.delete', $quiz->id));

        $response->assertRedirect(route('quiz.leaderboard', $quiz->id));
        $this->assertDatabaseMissing('results', ['quiz_id' => $quiz->id]);
    }
}
