<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class EmployeeImport implements WithMultipleSheets, SkipsUnknownSheets
{
    /**
     * Daftarkan sheet apa saja yang mau dibaca oleh sistem.
     * Nama di sebelah kiri HARUS sama persis dengan nama sheet di Excel.
     */
    public function sheets(): array
    {
        return [
            'HHES'      => new EmployeeSheetImport(),
            'KAI'       => new EmployeeSheetImport(),
            'WR & KAI'  => new EmployeeSheetImport(),
            'Medan-Jkt' => new EmployeeSheetImport(),
        ];
    }

    /**
     * Fungsi ini (dari SkipsUnknownSheets) berguna agar jika ada sheet lain
     * yang tidak didaftarkan (contoh: sheet 'GT_Custom'), sistem tidak akan error
     * dan sekadar mengabaikannya.
     */
    public function onUnknownSheet($sheetName): void
    {
        // Biarkan kosong agar sheet yang tidak dikenal diabaikan
    }
}