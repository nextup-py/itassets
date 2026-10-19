<?php

namespace Database\Factories;

use App\Models\ServiceContract;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceContractFactory extends Factory
{
    protected $model = ServiceContract::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Soporte anual de red',
                'Mantenimiento de servidores',
                'Garantía extendida notebooks',
                'Consultoría de infraestructura',
            ]),
            'contract_type' => $this->faker->randomElement(array_keys(ServiceContract::TYPES)),
            'supplier_id' => Supplier::factory(),
            'start_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'cost' => $this->faker->optional()->randomFloat(2, 100, 20000),
            'billing_cycle' => $this->faker->optional()->randomElement(array_keys(ServiceContract::BILLING_CYCLES)),
            'auto_renew' => $this->faker->boolean(),
            'sla' => $this->faker->optional()->randomElement([
                'Respuesta en 4 horas hábiles',
                'Soporte 24/7',
                'Próximo día hábil',
            ]),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
