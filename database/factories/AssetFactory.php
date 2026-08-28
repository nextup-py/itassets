<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'asset_tag' => $this->faker->unique()->bothify('IT-####'),
            'name' => $this->faker->randomElement([
                'Laptop', 'Desktop', 'Monitor', 'Teclado', 'Mouse',
                'Switch', 'Router', 'UPS', 'Impresora', 'Escáner',
                'Tablet', 'Teléfono IP', 'Servidor', 'Firewall',
            ]) . ' ' . $this->faker->company(),
            'asset_category_id' => AssetCategory::factory(),
            'brand' => $this->faker->optional()->randomElement(['Dell', 'HP', 'Lenovo', 'Apple', 'Samsung', 'Cisco', 'Brother']),
            'model' => $this->faker->optional()->bothify('??-####'),
            'serial_number' => $this->faker->optional()->bothify(strtoupper($this->faker->randomLetter()) . '##' . strtoupper($this->faker->randomLetter()) . '####'),
            'status' => $this->faker->randomElement(['stock', 'available', 'assigned', 'maintenance']),
            'condition' => $this->faker->randomElement(['new', 'good', 'fair']),
            'photo' => null,
            'notes' => $this->faker->optional()->sentence(),
            'purchase_date' => $this->faker->optional()->date(),
            'purchase_price' => $this->faker->optional()->randomFloat(2, 100, 50000),
            'supplier_id' => Supplier::factory(),
            'location_id' => Location::factory(),
            'warranty_expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+3 years'),
            'warranty_supplier_id' => null,
        ];
    }

    public function available(): static
    {
        return $this->state(['status' => 'available', 'condition' => 'good']);
    }

    public function assigned(): static
    {
        return $this->state(['status' => 'assigned', 'condition' => 'good']);
    }

    public function maintenance(): static
    {
        return $this->state(['status' => 'maintenance']);
    }

    public function retired(): static
    {
        return $this->state(['status' => 'retired', 'condition' => 'poor']);
    }

    public function lost(): static
    {
        return $this->state(['status' => 'lost']);
    }
}
