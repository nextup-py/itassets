<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseFactory extends Factory
{
    protected $model = License::class;

    public function definition(): array
    {
        return [
            'product_name' => $this->faker->randomElement([
                'Microsoft 365 Business',
                'Visual Studio Professional',
                'Adobe Creative Cloud',
                'JetBrains All Products',
                'Windows 11 Pro',
                'Slack Enterprise',
                'VMware vSphere',
                'Autodesk AutoCAD',
            ]),
            'license_type' => $this->faker->randomElement(array_keys(License::TYPES)),
            'license_key' => $this->faker->optional()->bothify('XXXXX-XXXXX-XXXXX-XXXXX-XXXXX'),
            'total_seats' => $this->faker->numberBetween(1, 100),
            'billing_cycle' => null,
            'auto_renew' => false,
            'purchase_date' => $this->faker->optional()->date(),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'purchase_price' => $this->faker->optional()->randomFloat(2, 100, 10000),
            'supplier_id' => Supplier::factory(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function perpetual(): static
    {
        return $this->state(['license_type' => 'perpetual', 'expiry_date' => null]);
    }

    public function subscription(): static
    {
        return $this->state([
            'license_type' => 'subscription',
            'billing_cycle' => $this->faker->randomElement(array_keys(License::BILLING_CYCLES)),
            'auto_renew' => $this->faker->boolean(),
            'expiry_date' => $this->faker->dateTimeBetween('now', '+1 year'),
        ]);
    }
}
