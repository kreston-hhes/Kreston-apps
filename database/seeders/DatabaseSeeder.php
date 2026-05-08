<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $partnerships = \App\Models\Partnership::factory(10)->create();
        $employees = \App\Models\Employee::factory(50)->create([
            'partnership_id' => $partnerships->random()->id,
        ]);

User::create([
            'first_name' => 'Administrator',
            'last_name' => 'Gaul',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'), // Password yang akan diketik nanti
            'active' => true, // Sesuai kolom di migrasi kamu
        ]);
    }
}
