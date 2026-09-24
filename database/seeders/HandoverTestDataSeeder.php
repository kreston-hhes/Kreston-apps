<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\AssetType;
use App\Models\AssetCategory;
use App\Models\Partnership;

class HandoverTestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Partner Khusus Testing
        $partnership = Partnership::updateOrCreate(
            ['code' => 'TEST-TIM'],
            [
                'name'  => 'Tim Uji Coba Internal',
                'nik'   => 'DUMMY-MGR-999',
                'email' => 'dummy.partner@kreston.id',
                'division'=>'IT'
            ]
        );
        // 2. Buat Kategori Khusus (Syarat mutlak: serah_terima)
        $category = AssetCategory::updateOrCreate(
            ['name' => 'Laptop Uji Coba'],
            [
                'category_code' => 'T', // <--- TAMBAHKAN BARIS INI
                'document_flow' => 'serah_terima'
            ]
        );
        // 3. Buat Tipe Khusus
        $type = AssetType::updateOrCreate(
            ['type_code' => 'TEST-LTP'],
            [
                'name' => 'Laptop Testing Lenovo', 
                // Jika error, coba ganti 'category_id' jadi 'asset_category_id' sesuai tabel kamu
                'category_id' => $category->id 
            ]
        );

        // 4. Buat Karyawan Testing 1 & 2 (Syarat mutlak: active)
        Employee::updateOrCreate(
            ['nik' => 'TEST-EMP-991'],
            [
                'first_name' => 'Karyawan',
                'last_name' => 'Testing Satu',
                'email' => 'test1@kreston.id',
                'position' => 'Staff Tester',
                'status' => 'active', 
                'partnership_id' => $partnership->id,
            ]
        );

        Employee::updateOrCreate(
            ['nik' => 'TEST-EMP-992'],
            [
                'first_name' => 'Karyawan',
                'last_name' => 'Testing Dua',
                'email' => 'test2@kreston.id',
                'position' => 'Staff Tester',
                'status' => 'active', 
                'partnership_id' => $partnership->id,
            ]
        );

        // 5. Buat Aset Testing 1 & 2 (Syarat mutlak: server_room)
        Asset::updateOrCreate(
            ['serial_number' => 'TEST-SN-991'],
            [
                'asset_code' => 'TEST-AST-991',
                'hostname' => 'LTP-TEST-01',
                'name' => 'ThinkPad Testing Alpha',
                'brand' => 'Lenovo',
                'processor' => 'Intel Core i5',
                'ram' => '16GB',
                'storage' => '512GB SSD',
                'placement_status' => 'server_room',
                'condition_status' => 'good',
                'type_id' => $type->id,
                'partnership_id' => $partnership->id,
            ]
        );

        Asset::updateOrCreate(
            ['serial_number' => 'TEST-SN-992'],
            [
                'asset_code' => 'TEST-AST-992',
                'hostname' => 'LTP-TEST-02',
                'name' => 'ThinkPad Testing Beta',
                'brand' => 'Lenovo',
                'processor' => 'Intel Core i7',
                'ram' => '16GB',
                'storage' => '1TB SSD',
                'placement_status' => 'server_room',
                'condition_status' => 'good',
                'type_id' => $type->id,
                'partnership_id' => $partnership->id,
            ]
        );

        $this->command->info('Data testing AMAN berhasil ditambahkan!');
    }
}