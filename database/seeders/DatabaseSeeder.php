<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Partnership;
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
   

User::create([
            'first_name' => 'Administrator',
            'last_name' => 'Gaul',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'), // Password yang akan diketik nanti
            'active' => true, // Sesuai kolom di migrasi kamu
        ]);

   Partnership::create([
            'nik' => 'EMP-0002',
            'code' => 'PTCP-001',
            'name' => 'PT. Contoh Perusahaan',
            'email' => 'john.doe@example.com',
            'phone' => '081234567890',
            'gender' => 'Male',
            'division' => 'IT',
            'date_of_entry' => now(),
            'release_date' => null,
            'status' => 'Active',
   ]);

        Employee::Create([
            'nik' => 'EMP-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '081234567890',
            'address' => '123 Main Street',
            'gender' => 'Male',
            'birth_date' => '1990-01-01',
            'position' => 'Manager',
            'division' => 'IT',
            'date_of_entry' => now(),
            'release_date' => null,
            'partnership_id' => 1,
            'manager_id' => null,
            'user_id' => 1,
            'status' => 'Active',
        ]);

    }
}
