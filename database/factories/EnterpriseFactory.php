<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enterprise>
 */
class EnterpriseFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name' => fake()->company(),
      // 'code' => rand(10000,99999),
      // 'code_id' => rand(2,99),
      'code_id' => fake()->unique()->numberBetween(0,99),
      'address' => json_encode([
        "city" => fake()->city(),
        "country" => fake()->country(),
        // 'department' => '',
        // 'street' => '',
        // 'postOfficeBox' => '',
        'postalZipCode' => fake()->postcode(),
        // 'state' => '',
        // 'province' => '',
        // 'building' => '',
        // 'room' => '',
        'phoneNumber' => [fake()->phoneNumber(), fake()->phoneNumber()],
        'faxNumber' => [],
        'email' => [fake()->email(), fake()->email()],
        'internet' => [fake()->url(), fake()->url()],
        'SITA' => '',
      ]),
    ];
  }
}
