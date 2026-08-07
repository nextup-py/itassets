<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'legajo' => $this->faker->unique()->numerify('EMP-####'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->optional()->numerify('###-###-####'),
            'department_id' => Department::factory(),
            'position' => $this->faker->jobTitle(),
            'document_number' => $this->faker->unique()->numerify('########'),
            'document_type' => $this->faker->randomElement(array_keys(Employee::DOCUMENT_TYPES)),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
