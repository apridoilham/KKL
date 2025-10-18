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
        $itemType = $this->faker->randomElement(['barang_mentah', 'barang_jadi']);
        $quantity = $this->faker->numberBetween(0, 100);

        return [
            'name' => $this->faker->words(3, true),
            // 'category' => $this->faker->word, // <-- Hapus baris ini
            'code' => strtoupper($this->faker->unique()->bothify('???-###')),
            'item_type' => $itemType,
            'harga_beli' => $itemType === 'barang_mentah' ? $this->faker->numberBetween(1000, 50000) : null, // Contoh harga beli
            'harga_jual' => $itemType === 'barang_jadi' ? $this->faker->numberBetween(50000, 200000) : null, // Contoh harga jual
            'quantity' => $quantity,
            'status' => $quantity > 0 ? 'available' : 'out', // Status berdasarkan quantity
        ];
    }
}