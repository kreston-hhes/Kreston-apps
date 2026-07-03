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
        $bulan = $purchaseDate->format('m'); // Contoh: 07
        $tahun = $purchaseDate->format('y'); // Contoh: 26

        // 2. Ambil Kode Partner dari relasi partnership
        // Pastikan model Asset sudah memiliki relasi 'partnership'
        $partnerCode = strtoupper($asset->partnership->code); // Contoh: KAP

        // 3. Ambil Kode Kategori (H/S/N) dan Jenis (LPT/PCX)
        // Kita berasumsi model Asset berelasi ke 'type' yang berelasi ke 'category'
        $categoryCode = strtoupper($asset->type->category->category_code); // Contoh: H
        $typeCode = strtoupper($asset->type->type_code); // Contoh: LPT

        // Prefix dasar tanpa nomor urut (Contoh: KAPH0726)
        // Kita gunakan substring untuk nomor urut berdasarkan prefix ini agar reset per bulan/tahun/partner
        $prefix = $partnerCode . $categoryCode . $bulan . $tahun;

        // 4. Cari nomor urut terakhir di database yang memiliki awalan prefix tersebut
        $lastAsset = Asset::where('asset_code', 'LIKE', $prefix . '%')
                            ->orderBy('asset_code', 'desc')
                            ->first();

        if ($lastAsset) {
            // Mengambil 4 digit terakhir dari asset_code lama, lalu ditambah 1
            $lastNumber = (int) substr($lastAsset->asset_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format nomor urut menjadi 4 digit dengan padding nol di depan (Contoh: 0001)
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // 5. Gabungkan semua komponen menjadi Hostname Utama tanpa simbol
        // Contoh Akhir: KAPH07260001 (Panjang pas 12-15 karakter, aman untuk hostname)
        $asset->asset_code = $prefix . $formattedNumber;
    }
}
