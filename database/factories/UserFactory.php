<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'first_name' => fake()->firstName(),
      'middle_name' => fake()->lastName(),
      'last_name' => fake()->lastName(),
      'job_title' => fake()->jobTitle(),
      'work_enterprise_id' => rand(1,3),
      'email' => fake()->unique()->safeEmail(),
      'password' => '$2y$10$BkzZhuRUrW2UWnGzQmGWLOIMj4P17o9lRH1HoSx7qHubAyYH8T/7q', // 'password'
      'storage' => Str::random(5),
      'address' => ([
        "city" => fake()->city(),
        "country" => fake()->country()
      ]),
      'remember_token' => Str::random(10)
    ];
  }

  /**
   * Indicate that the model's email address should be unverified.
   */
  public function unverified(): static
  {
    return $this->state(fn (array $attributes) => [
      'email_verified_at' => null,
    ]);
  }
}
