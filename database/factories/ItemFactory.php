<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'category' => $this->faker->word,
            'code' => strtoupper($this->faker->unique()->bothify('???-###')),
            'item_type' => $this->faker->randomElement(['barang_mentah', 'barang_jadi']),
            'quantity' => $this->faker->numberBetween(0, 100),
            'status' => 'available',
        ];
    }
}