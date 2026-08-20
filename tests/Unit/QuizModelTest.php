<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class QuizModelTest extends TestCase
{
    public function test_quiz_has_fillable_attributes(): void
    {
        $quiz = new Quiz([
            'title' => 'Test Quiz',
            'description' => 'Test Description',
            'start_datetime' => '2026-08-20 10:00:00',
            'duration' => 120,
            'userid' => 1,
        ]);

        $this->assertEquals('Test Quiz', $quiz->title);
        $this->assertEquals('Test Description', $quiz->description);
        $this->assertEquals('2026-08-20 10:00:00', $quiz->start_datetime);
        $this->assertEquals(120, $quiz->duration);
        $this->assertEquals(1, $quiz->userid);
    }

    public function test_quiz_questions_relationship(): void
    {
        $quiz = new Quiz;
        $relation = $quiz->questions();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Question::class, $relation->getRelated());
    }

    public function test_quiz_teacher_relationship(): void
    {
        $quiz = new Quiz;
        $relation = $quiz->teacher();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
        $this->assertEquals('userid', $relation->getForeignKeyName());
    }
}
