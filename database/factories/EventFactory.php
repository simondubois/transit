<?php

namespace Database\Factories;

use App\Enums\RideSyncStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $date = fake()->date();
        $endTime = fake()->time();
        $startTime = fake()->time('H:i:s', $endTime);

        return [
            'name' => fake()->company,
            'description' => fake()->paragraph,
            'location' => fake()->address,
            'incoming_ride_sync_status' => fake()->randomElement(RideSyncStatus::class),
            'outgoing_ride_sync_status' => fake()->randomElement(RideSyncStatus::class),
            'start' => "$date $startTime",
            'end' => "$date $endTime",
        ];
    }
}
