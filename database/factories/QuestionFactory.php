<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'text' => fake()->sentence().'?',
            'option1' => fake()->word(),
            'option2' => fake()->word(),
            'option3' => fake()->word(),
            'option4' => fake()->word(),
            'right_option' => (string) fake()->numberBetween(1, 4),
            'duration' => 30,
            'image' => null,
        ];
    }
}
