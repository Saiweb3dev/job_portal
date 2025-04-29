<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'user_id' => User::factory()->state(['role' => 'employer']),
        'category_id' => Category::factory(),
        'title' => $this->faker->sentence(4),
        'description' => $this->faker->paragraph(4),
        'location' => $this->faker->city,
        'salary' => $this->faker->numberBetween(30000, 100000),
        'type' => $this->faker->randomElement(['full-time', 'part-time', 'remote', 'contract']),
    ];
}
}
