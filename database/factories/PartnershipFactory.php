<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partnership>
 */
use App\Models\Partnership;

class PartnershipFactory extends Factory
{
    protected $model = Partnership::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'nik' => fake()->unique()->numerify('KR-####'),
        'code' => fake()->unique()->numerify('P-####'),
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'phone' => fake()->phoneNumber(),
        'address' => fake()->address(),
        'gender' => fake()->randomElement(['male', 'female']),
        'birth_date' => fake()->date(),
        'position' => fake()->randomElement(['Manager', 'Supervisor', 'Staff', 'Intern']),
        'division' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing']),
        'date_of_entry' => fake()->date(),
        'release_date' => null, // Default kosong
        ];
    }
}
