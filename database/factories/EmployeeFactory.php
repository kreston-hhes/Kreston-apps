<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
use App\Models\Employee;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
              'nik' => fake()->unique()->numerify('EMP-####'),
              'first_name' => fake()->firstName(),
              'last_name' => fake()->lastName(),
              'email' => fake()->unique()->safeEmail(),
              'phone' => fake()->phoneNumber(),
              'address' => fake()->address(),
              'gender' => fake()->randomElement(['male', 'female']),
              'birth_date' => fake()->date(),
              'position' => fake()->randomElement(['Manager', 'Supervisor', 'Staff', 'Intern']),
              'division' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing']),
              'date_of_entry' => fake()->date(),
              'release_date' => null,
                'partnership_id' => fake()->randomElement([1, 2, 3, 4, null]), // Asumsikan ada beberapa partnership dengan ID ini
              'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
