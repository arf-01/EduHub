<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class QuestionStudioTest extends TestCase
{
    use DatabaseTransactions;

    public function test_teacher_can_add_question_with_code_and_text()
    {
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'C++ Masterclass',
            'userid' => $teacher->id,
            'start_datetime' => now(),
            'duration' => -1,
        ]);

        $codeText = "What is the output?\n\n```cpp\n#include <iostream>\nusing namespace std;\nint main() {\n    cout << 42;\n}\n```";

        $response = $this->actingAs($teacher)->postJson(route('questions.add'), [
            'quiz_id' => $quiz->id,
            'question_text' => $codeText,
            'options' => [
                1 => '42',
                2 => '0',
                3 => 'Compilation error',
                4 => 'Runtime error'
            ],
            'correct_option' => 1,
            'duration' => 45,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'text' => $codeText,
            'option1' => '42',
            'right_option' => 1,
            'duration' => 45,
        ]);

        $this->assertDatabaseHas('questions', [
            'quiz_id' => $quiz->id,
            'option1' => '42',
            'right_option' => 1,
            'duration' => 45,
        ]);
    }

    public function test_teacher_can_add_question_with_image()
    {
        Storage::fake('public');
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'Diagram Quiz',
            'userid' => $teacher->id,
            'start_datetime' => now(),
            'duration' => -1,
        ]);

        $file = UploadedFile::fake()->create('diagram.png', 100, 'image/png');

        $response = $this->actingAs($teacher)->postJson(route('questions.add'), [
            'quiz_id' => $quiz->id,
            'question_text' => 'What does this UML diagram represent?',
            'question_image' => $file,
            'options' => [
                1 => 'Inheritance',
                2 => 'Aggregation',
                3 => 'Composition',
                4 => 'Dependency'
            ],
            'correct_option' => 3,
            'duration' => 60,
        ]);

        $response->assertStatus(200);
        $question = Question::where('quiz_id', $quiz->id)->first();
        $this->assertNotNull($question);
        $this->assertNotNull($question->image);
        Storage::disk('public')->assertExists($question->image);
    }

    public function test_teacher_can_update_question()
    {
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'Algorithms',
            'userid' => $teacher->id,
            'start_datetime' => now(),
            'duration' => -1,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'text' => 'Initial question',
            'option1' => 'A',
            'option2' => 'B',
            'option3' => 'C',
            'option4' => 'D',
            'right_option' => 1,
            'duration' => 30,
        ]);

        $response = $this->actingAs($teacher)->putJson(route('questions.update', $question->id), [
            'question_text' => 'Updated question text',
            'options' => [
                1 => 'Alpha',
                2 => 'Beta',
                3 => 'Gamma',
                4 => 'Delta'
            ],
            'correct_option' => 2,
            'duration' => 90,
        ]);

        $response->assertStatus(200);
        $question->refresh();
        $this->assertEquals('Updated question text', $question->text);
        $this->assertEquals('Beta', $question->option2);
        $this->assertEquals(2, $question->right_option);
        $this->assertEquals(90, $question->duration);
    }

    public function test_unauthorized_user_cannot_update_or_delete_question()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $quiz = Quiz::create([
            'title' => 'Private Quiz',
            'userid' => $owner->id,
            'start_datetime' => now(),
            'duration' => -1,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'text' => 'Secure question',
            'option1' => '1',
            'option2' => '2',
            'option3' => '3',
            'option4' => '4',
            'right_option' => 1,
            'duration' => 30,
        ]);

        // Attempt update by unauthorized user
        $response = $this->actingAs($otherUser)->putJson(route('questions.update', $question->id), [
            'question_text' => 'Hacked question',
            'options' => [1 => '1', 2 => '2', 3 => '3', 4 => '4'],
            'correct_option' => 1,
            'duration' => 30,
        ]);
        $response->assertStatus(403);

        // Attempt delete by unauthorized user
        $deleteResponse = $this->actingAs($otherUser)->deleteJson(route('questions.delete', $question->id));
        $deleteResponse->assertStatus(403);

        $this->assertDatabaseHas('questions', ['id' => $question->id]);
    }

    public function test_teacher_can_delete_own_question()
    {
        $teacher = User::factory()->create();
        $quiz = Quiz::create([
            'title' => 'My Quiz',
            'userid' => $teacher->id,
            'start_datetime' => now(),
            'duration' => -1,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'text' => 'To be deleted',
            'option1' => '1',
            'option2' => '2',
            'option3' => '3',
            'option4' => '4',
            'right_option' => 1,
            'duration' => 30,
        ]);

        $response = $this->actingAs($teacher)->deleteJson(route('questions.delete', $question->id));
        $response->assertStatus(200);

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    public function test_student_join_receives_correct_question_duration()
    {
        $teacher = User::factory()->create([
            'room_name' => 'CS90'
        ]);

        $quiz = Quiz::create([
            'title' => 'Speed Quiz',
            'userid' => $teacher->id,
            'start_datetime' => now()->subMinute(),
            'duration' => 10,
        ]);

        Question::create([
            'quiz_id' => $quiz->id,
            'text' => 'Question with 90 seconds',
            'option1' => 'A',
            'option2' => 'B',
            'option3' => 'C',
            'option4' => 'D',
            'right_option' => 1,
            'duration' => 90,
        ]);

        $response = $this->postJson("/api/quiz/start", [
            'quiz_id' => $quiz->id,
            'student_id' => 'STU999',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('questions.0.duration', 90);
    }
}
