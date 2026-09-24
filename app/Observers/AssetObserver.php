<?php

namespace App\Observers;

use App\Models\Asset;
use Carbon\Carbon;

class AssetObserver
{
    /**
     * Handle the Asset "creating" event.
     */
    public function creating(Asset $asset)
    {
        // 1. Ambil tanggal pembelian dari input user
        $purchaseDate = Carbon::parse($asset->purchase_date);
        $bulan = $purchaseDate->format('m'); // Contoh: 08
        $tahun = $purchaseDate->format('Y'); // Contoh: 2026 (4 digit penuh)

       // 2. Ambil Kode Partner langsung dari kolom relasinya
        $partner = $asset->partnership;
        $kodePartner = 'XXX';
        
        if ($partner) {
            // Cek apakah di database tabel partnerships sudah ada kolom 'code' (misal: 'LW' untuk Bu Liyanti)
            if (!empty($partner->code)) {
                $kodePartner = strtoupper($partner->code);
            } else {
                // Cadangan otomatis jika kolom code kosong: Ambil 3 huruf depan
                $kodePartner = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $partner->name), 0, 3));
            }
        }

        // 3. Ambil Kode Tipe Aset (LPT, MOU, KYB, dll)
        $tipeAset = $asset->type;
        $kodeTipe = 'XXX';
        if ($tipeAset) {
            $namaTipe = strtoupper($tipeAset->name);
            if (str_contains($namaTipe, 'LAPTOP')) {
                $kodeTipe = 'LPT';
            } elseif (str_contains($namaTipe, 'MOUSE')) {
                $kodeTipe = 'MOU';
            } elseif (str_contains($namaTipe, 'KEYBOARD')) {
                $kodeTipe = 'KYB';
            } else {
                $kodeTipe = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $namaTipe), 0, 3));
            }
        }

        // 4. Susun Prefix Utama (Contoh: EAWLPT082026)
        $prefix = $kodePartner . $kodeTipe . $bulan . $tahun;

        // 5. Cari nomor urut terakhir berdasarkan prefix tersebut
        $lastAsset = Asset::where('asset_code', 'LIKE', $prefix . '%')
                            ->orderBy('asset_code', 'desc')
                            ->first();

        if ($lastAsset) {
            // Mengambil 3 digit terakhir dari asset_code lama, lalu ditambah 1
            $lastNumber = (int) substr($lastAsset->asset_code, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format nomor urut menjadi 3 digit (Contoh: 001)
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // 6. Masukkan hasil akhir ke asset_code
        // Hasil akhir: EAWLPT082026001
        $asset->asset_code = $prefix . $formattedNumber;
    }
}