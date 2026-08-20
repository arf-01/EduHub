<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Result;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Result>
 */
class ResultFactory extends Factory
{
    protected $model = Result::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'student_id' => (string) fake()->numberBetween(2107001, 2107999),
            'device_id' => Str::uuid()->toString(),
            'score' => fake()->randomFloat(2, 0, 10),
        ];
    }
}
