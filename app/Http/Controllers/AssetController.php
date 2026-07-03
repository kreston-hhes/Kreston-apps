<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Partnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    /**
     * Menampilkan Daftar Laporan Aset IT
     */
    public function index()
    {
        // Mengambil semua data aset beserta relasi pendukungnya
        // 'currentAssignment.employee.user' digunakan untuk mengambil data user login yang membawa aset tersebut
        $assets = Asset::with(['partnership', 'type.category', 'currentAssignment.employee.user'])->get();

        // Kirim data ke view (silakan sesuaikan nama file blade Anda nanti)
        return view('pages.it.assets', compact('assets'));
    }

    /**
     * Menyimpan Data Aset Baru (Auto-generate Hostname via Observer)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input dari Form
        $request->validate([
            'partnership_id'   => 'required|exists:partnerships,id',
            'type_id'          => 'required|exists:asset_types,id',
            'name'             => 'required|string|max:191',
            'serial_number'    => 'nullable|string|max:191',
            'specification'    => 'required|string',
            'purchase_date'    => 'required|date',
            'warranty_expired' => 'nullable|date|after_or_equal:purchase_date',
        ]);

        try {
            // Gunakan Database Transaction agar aman jika terjadi kegagalan sistem
            DB::beginTransaction();

            // 2. Simpan data ke tabel assets
            // Kita CUKUP memasukkan inputan user saja.
            // Kolom 'asset_code' (hostname) akan otomatis diisi oleh AssetObserver di latar belakang.
            $asset = Asset::create([
                'partnership_id'   => $request->partnership_id,
                'type_id'          => $request->type_id,
                'name'             => $request->name,
                'serial_number'    => $request->serial_number,
                'specification'    => $request->specification,
                'purchase_date'    => $request->purchase_date,
                'warranty_expired' => $request->warranty_expired,
                'condition_status' => 'good', // Default kondisi awal bagus
                'placement_status' => 'it_room', // Default awal masuk ke gudang/ruang IT
            ]);

            DB::commit();

            // Kembalikan response sukses beserta hostname yang baru tercipta
            return redirect()->route('assets.index')
                             ->with('success', "Aset berhasil disimpan dengan Hostname: {$asset->asset_code}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Gagal menyimpan aset: ' . $e->getMessage());
        }
    }
}
