<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AssetCategory;
use App\Models\AssetType;

class AssetMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Kategori Utama & Jenis Aset di dalamnya
        $categories = [
            [
                'name' => 'Hardware',
                'category_code' => 'H',
                'types' => [
                    ['name' => 'Laptop', 'type_code' => 'LPT'],
                    ['name' => 'PC Desktop', 'type_code' => 'PCX'],
                    ['name' => 'Monitor', 'type_code' => 'MON'],
                    ['name' => 'Printer', 'type_code' => 'PRN'],
                    ['name' => 'Server', 'type_code' => 'SVR'],
                ]
            ],
            [
                'name' => 'Software',
                'category_code' => 'S',
                'types' => [
                    ['name' => 'Operating System', 'type_code' => 'OSX'],
                    ['name' => 'Office Suite', 'type_code' => 'OFX'],
                    ['name' => 'Design Software', 'type_code' => 'CAD'],
                ]
            ],
            [
                'name' => 'Network',
                'category_code' => 'N',
                'types' => [
                    ['name' => 'Router', 'type_code' => 'RTR'],
                    ['name' => 'Switch', 'type_code' => 'SWT'],
                    ['name' => 'Access Point', 'type_code' => 'APX'],
                ]
            ],
        ];

        // 2. Loop untuk memasukkan data ke Database
        foreach ($categories as $catData) {
            // Simpan Kategori
            $category = AssetCategory::create([
                'name' => $catData['name'],
                'category_code' => $catData['category_code'],
            ]);

            // Simpan Jenis Aset yang terhubung ke Kategori di atas
            foreach ($catData['types'] as $typeData) {
                AssetType::create([
                    'category_id' => $category->id,
                    'name' => $typeData['name'],
                    'type_code' => $typeData['type_code'],
                ]);
            }
        }
    }
}
