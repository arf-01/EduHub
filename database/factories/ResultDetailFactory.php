<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Result;
use App\Models\ResultDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResultDetail>
 */
class ResultDetailFactory extends Factory
{
    protected $model = ResultDetail::class;

    public function definition(): array
    {
        return [
            'result_id' => Result::factory(),
            'question_id' => Question::factory(),
            'selected_option' => fake()->numberBetween(1, 4),
        ];
    }
}
