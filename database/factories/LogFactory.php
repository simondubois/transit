<?php

namespace Database\Factories;

use App\Enums\LogStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Log>
 */
class LogFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'job_type' => fake()->name,
            'job_started_at' => fake()->dateTime(),
            'status' => fake()->randomElement(LogStatus::class),
        ];
    }
}
