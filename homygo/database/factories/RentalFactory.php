<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rental>
 */
class RentalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'title'         => $this->faker->sentence(3),
        'description'   => $this->faker->paragraph(),
        'barangay'      => $this->faker->randomElement(['Carmen', 'Nazareth', 'Kauswagan', 'Iponan']),
        'property_type' => $this->faker->randomElement(['apartment', 'studio', 'house']),
        'price'         => $this->faker->numberBetween(5000, 30000),
        'image'         => $this->faker->imageUrl(640, 480, 'house', true),
    ];
}
}
