<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Partnership;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AssetsImport implements ToModel, WithHeadingRow
{
    /**
     * Memetakan setiap baris Excel ke dalam database Asset
     */
    public function model(array $row): ?Model
    {   
        // 1. Ambil Singkatan Partner (3 Huruf)
        $partner = Partnership::find($row['partner_id'] ?? null);
        if ($partner) {
            $namaPartner = str_replace(['PT.', 'PT ', 'CV.', 'CV '], '', $partner->name);
            $kodePartner = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $namaPartner), 0, 3));
        } else {
            $kodePartner = 'XXX';
        }

        // 2. Ambil Singkatan Tipe Aset
        $tipeAset = AssetType::find($row['kategori_id'] ?? null);
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

        // 3. Ambil Bulan & Tahun (Jika di Excel kosong, gunakan bulan/tahun saat ini)
        $tanggalBeli = !empty($row['tanggal_pembelian']) ? Carbon::parse($row['tanggal_pembelian']) : Carbon::now();
        $bulanTahun  = $tanggalBeli->format('mY');

        // 4. Gabungkan Prefix Tanpa Spasi/Strip 
        $prefixCode = $kodePartner . $kodeTipe . $bulanTahun;

        // 5. Hitung Nomor Urut di Database
        $jumlahAset = Asset::where('asset_code', 'like', $prefixCode . '%')->count() + 1;
        $nomorUrut  = str_pad($jumlahAset, 3, '0', STR_PAD_LEFT); 

        // 6. Finalisasi Kode
        $generatedAssetCode = $prefixCode . $nomorUrut;

        return new Asset([
            'partnership_id'   => $row['partner_id'] ?? null,
            'type_id'          => $row['kategori_id'],
            'name'             => $row['nama_perangkat'],
            'asset_code'       => $generatedAssetCode, // <-- Masukkan kode otomatis di sini!
            'serial_number'    => $row['serial_number'] ?? null,
            'vendor'           => $row['vendor'] ?? null,
            'purchase_date'    => !empty($row['tanggal_pembelian']) ? $row['tanggal_pembelian'] : null,
            'warranty_expired' => !empty($row['garansi_habis']) ? $row['garansi_habis'] : null,
            'specification'    => json_encode(['Merk' => $row['merk'] ?? '', 'Tipe' => $row['tipe'] ?? '']),
            
            // Default status sama seperti di Controller kamu
            'condition_status' => 'good',
            'placement_status' => 'server_room',
            'qc_status'        => 'passed',
        ]);
    }
}