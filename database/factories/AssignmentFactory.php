<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'assigned_by' => $this->faker->name(),
            'assigned_at' => $this->faker->date(),
            'returned_at' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function returned(): static
    {
        return $this->state(fn () => [
            'returned_at' => $this->faker->date(),
            'notes' => $this->faker->optional(0.7)->sentence() . "\n[Devolución] " . $this->faker->sentence(),
        ]);
    }

    public function toDepartment(): static
    {
        return $this->state(fn () => [
            'employee_id' => null,
            'department_id' => Department::factory(),
        ]);
    }
}
