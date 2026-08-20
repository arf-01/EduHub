<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'userid' => User::factory(),
            'start_datetime' => now(),
            'duration' => 60,
        ];
    }

    public function unscheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_datetime' => null,
        ]);
    }
}
