<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Partnership;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeSheetImport implements ToModel, WithHeadingRow
{
    /**
     * Memetakan setiap baris Excel ke dalam model Employee.
     * Error garis bawah merah pada fungsi model() ini adalah bug Intelephense, abaikan saja.
     */
    public function model(array $row): ?Model
    {
        // Abaikan baris jika NIK atau Nama kosong (mencegah error pada baris kosong di ujung Excel)
        if (empty($row['nik']) || empty($row['nama'])) {
            return null;
        }

        // 1. Pecah nama lengkap dari Excel menjadi first_name dan last_name
        $namaLengkap = trim($row['nama']);
        $parts = explode(' ', $namaLengkap, 2); 
        $firstName = $parts[0];
        $lastName = $parts[1] ?? null; 

        // 2. Konversi teks nama Partner di Excel menjadi ID (partnership_id)
        $partnerId = null;
        if (!empty($row['partner'])) {
            $partner = Partnership::query()->where('name', 'like', '%' . trim($row['partner']) . '%')->first();
            $partnerId = $partner ? $partner->id : null;
        }

        // 3. Konversi teks nama Manager di Excel menjadi ID (manager_id)
        $managerId = null;
        if (!empty($row['manager'])) {
            $manager = Employee::query()->where('first_name', 'like', '%' . trim($row['manager']) . '%')->first();
            $managerId = $manager ? $manager->id : null;
        }

        // 4. Masukkan ke database
        return new Employee([
            'nik'            => $row['nik'],
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'position'       => $row['jabatan'] ?? null,
            'division'       => $row['bagian'] ?? null,
            'partnership_id' => $partnerId,
            'manager_id'     => $managerId,
            'status'         => 'active',
            
            // Dikosongkan karena tidak ada datanya di file Excel
            'email'          => null,
            'phone'          => null,
            'address'        => null,
            'gender'         => null,
            'birth_date'     => null,
            'date_of_entry'  => null,
            'release_date'   => null,
            'user_id'        => null,
        ]);
    }
}