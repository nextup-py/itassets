<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word() . ' ' . $this->faker->randomElement(['Equipos', 'Accesorios', 'Sistemas']),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    public function hardware(): static
    {
        return $this->state(['name' => 'Hardware']);
    }

    public function software(): static
    {
        return $this->state(['name' => 'Software / Licencias']);
    }

    public function mobile(): static
    {
        return $this->state(['name' => 'Dispositivos móviles', 'is_mobile' => true]);
    }

    public function networkEquipment(): static
    {
        return $this->state(['name' => 'Equipos de red', 'is_network_equipment' => true]);
    }
}
