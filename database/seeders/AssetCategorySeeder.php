<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetCategory;
use App\Models\AssetType;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Laptop',
                'category_code' => 'H',
                'document_flow' => 'serah_terima',
                'spec_template' => [
                    'Merk', 'Tipe', 'Processor', 'RAM', 'Storage', 'OS',
                    'Graphics', 'Display', 'Slot SATA', 'Tas', 'Wifi', 'Lan',
                    'IP Address LAN', 'LAN Mac', 'IP Address WLAN', 'WLAN Mac',
                    'Wlan Mac Radmin', 'Notebook ID', 'User Windows',
                    'Pin Windows', 'Microsoft Product Key',
                ],
            ],
            [
                'name' => 'Mouse',
                'category_code' => 'H',
                'document_flow' => 'skppi',
                'spec_template' => ['Merk', 'Tipe'],
            ],
            [
                'name' => 'Flashdisk',
                'category_code' => 'H',
                'document_flow' => 'skppi',
                'spec_template' => ['Merk', 'Tipe', 'Kapasitas'],
            ],
            [
                'name' => 'Printer',
                'category_code' => 'H',
                'document_flow' => 'serah_terima',
                'spec_template' => ['Merk', 'Tipe', 'Jenis Warna', 'IP Address (Static)'],
            ],
            [
                'name' => 'Proyektor',
                'category_code' => 'H',
                'document_flow' => 'logbook',
                'spec_template' => ['Merk', 'Tipe', 'Lumens'],
            ],
        ];

        foreach ($categories as $categoryData) {
            // 1. Buat atau perbarui Kategorinya
            $category = AssetCategory::updateOrCreate(
                ['name' => $categoryData['name']],
                [
                    'category_code' => $categoryData['category_code'],
                    'document_flow' => $categoryData['document_flow'],
                    // 'spec_template' => $categoryData['spec_template'], // Buka komen ini jika ada kolom spec_template di model Category
                ]
            );

            // 2. KUNCI UTAMANYA: Buat Tipenya dan tautkan category_id ke Kategori di atas!
            AssetType::updateOrCreate(
                ['name' => $categoryData['name']],
                [
                    'category_id'   => $category->id, // Ini yang akan menghilangkan error "null"
                    'type_code'     => strtoupper(substr($categoryData['name'], 0, 3)),
                    'document_flow' => $categoryData['document_flow'],
                ]
            );
        }
    }
}