<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\Supply;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplyFactory extends Factory
{
    protected $model = Supply::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Disco duro 1TB', 'Memoria RAM 8GB', 'Cable de red',
                'Mouse inalámbrico', 'Teclado USB', 'Cargador universal',
                'Batería de notebook', 'Cartucho de tóner',
            ]),
            'asset_category_id' => AssetCategory::factory(),
            'quantity_available' => $this->faker->numberBetween(0, 50),
            'quantity_damaged' => 0,
            'supplier_id' => Supplier::factory(),
            'location_id' => Location::factory(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
