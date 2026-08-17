<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class QuizScheduleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_teacher_can_schedule_quiz()
    {
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'Biology Quiz',
            'userid' => $teacher->id,
            'start_datetime' => null,
            'duration' => 60,
        ]);

        Question::create([
            'quiz_id' => $quiz->id,
            'text' => 'Sample Question',
            'option1' => 'A',
            'option2' => 'B',
            'option3' => 'C',
            'option4' => 'D',
            'right_option' => 1,
            'duration' => 45,
        ]);

        $futureDate = Carbon::now()->addDays(2)->format('Y-m-d H:i');

        $response = $this->actingAs($teacher)->post(route('quiz.schedule', $quiz->id), [
            'start_datetime' => $futureDate,
        ]);

        $response->assertSessionHas('success');
        $quiz->refresh();
        $this->assertNotNull($quiz->start_datetime);
        $this->assertEquals(45, $quiz->duration);
    }

    public function test_teacher_can_clear_schedule()
    {
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'Chemistry Quiz',
            'userid' => $teacher->id,
            'start_datetime' => now()->addHour(),
            'duration' => 60,
        ]);

        $response = $this->actingAs($teacher)->post(route('quiz.schedule', $quiz->id), [
            'start_datetime' => '',
        ]);

        $response->assertSessionHas('success');
        $quiz->refresh();
        $this->assertNull($quiz->start_datetime);
    }
}
