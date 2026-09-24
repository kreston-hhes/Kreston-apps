<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Partnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with([
            'partnership',
            'type.category',
            'currentAssignment.employee',

            // seluruh riwayat pemakaian (bukan cuma yang aktif), buat ditampilin
            // urut di modal detail - nama, tanggal serah & kembali per orang
            'assignments' => function ($q) {
                $q->with('employee')->latest('assigned_at');
            },
            'latestLoan' // <--- Mengambil data peminjam terakhir dari logbook
        ]);

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter Berdasarkan Tipe/Kategori (Dropdown baru)
        if ($request->filled('category')) {
            $query->where('type_id', $request->category);
        }

        // Filter Berdasarkan Partnership
        if ($request->filled('partnership')) {
            $query->where('partnership_id', $request->partnership);
        }

        if ($request->filled('placement_status')) {
            $query->where('placement_status', $request->placement_status);
        }

        $assets = $query->latest()->paginate(15)->withQueryString();

        $categories = AssetCategory::with('types')->orderBy('name')->get();
        $partnerships = Partnership::where('status', 'active')->orderBy('name')->get();

        return view('pages.it.assets', compact('assets', 'categories', 'partnerships'));
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'partnership',
            'type.category',
            'assignments' => function ($query) {
                $query->with('employee')->latest('assigned_at');
            },
            'latestLoan', // <--- Memuat riwayat peminjaman logbook di halaman detail
            'consumables',
            'invoices',
        ]);

        return view('pages.it.asset-detail', compact('asset'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'partnership_id'   => 'nullable|exists:partnerships,id',
            'type_id'          => 'required|exists:asset_types,id',
            'name'             => 'required|string|max:191',
            'serial_number'    => 'nullable|string|max:191',
            'specification'    => 'nullable|string',
            'purchase_date'    => 'nullable|date',
            'warranty_expired' => 'nullable|date|after_or_equal:purchase_date',
            'vendor'           => 'nullable|string|max:191',
            'purchased_by'     => 'nullable|string|max:191',
        ]);

        try {
            DB::beginTransaction();

            $specification = $request->specification
                ? json_decode($request->specification, true)
                : null;

            // --- 🟢 LOGIKA PEMBUAT KODE ASET OTOMATIS 🟢 ---
            $tipeAset = \App\Models\AssetType::with('category')->find($request->type_id);
            
            // 1. Ambil Singkatan Partner (3 Huruf)
            $partner = \App\Models\Partnership::find($request->partnership_id);
            if ($partner) {
                // Buang kata "PT." atau "CV." agar singkatannya akurat
                $namaPartner = str_replace(['PT.', 'PT ', 'CV.', 'CV '], '', $partner->name);
                // Ambil 3 huruf pertama
                $kodePartner = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $namaPartner), 0, 3));
            } else {
                $kodePartner = 'XXX';
            }

            // 2. Ambil Singkatan Tipe Aset
            $tipeAset = \App\Models\AssetType::find($request->type_id);
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

            // 3. Ambil Bulan & Tahun dari Purchase Date (Format: MMYYYY)
            $tanggalBeli = \Carbon\Carbon::parse($request->purchase_date);
            $bulanTahun  = $tanggalBeli->format('mY');

            // 4. Gabungkan Prefix Tanpa Spasi/Strip 
            $prefixCode = $kodePartner . $kodeTipe . $bulanTahun;

            // 5. Hitung Nomor Urut
            $jumlahAset = \App\Models\Asset::where('asset_code', 'like', $prefixCode . '%')->count() + 1;
            $nomorUrut  = str_pad($jumlahAset, 3, '0', STR_PAD_LEFT); 

            // 6. Gabungkan Prefix + Nomor Urut
            $generatedAssetCode = $prefixCode . $nomorUrut;
            
            // ---------------------------------------------------------
            $asset = Asset::create([
                'partnership_id'   => $request->partnership_id,
                'type_id'          => $request->type_id,
                'name'             => $request->name,
                'asset_code'       => $generatedAssetCode, 
                'serial_number'    => $request->serial_number,
                'specification'    => $specification, 
                'purchase_date'    => $request->purchase_date,
                'warranty_expired' => $request->warranty_expired ?: null,
                'vendor'           => $request->vendor,
                'purchased_by'     => $request->purchased_by,
                'condition_status' => 'good',
                'placement_status' => 'server_room',
                'qc_status'        => 'passed',
            ]);

            DB::commit();

            return redirect()->route('assets.index')
                             ->with('success', "Aset berhasil disimpan dengan Hostname: {$asset->asset_code}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Gagal menyimpan aset: ' . $e->getMessage());
        }
    }

    public function storeCategory(Request $request)
    {
        // Validasi disesuaikan: Hanya serah_terima (Surat Penyerahan umum) dan logbook (Proyektor)
        $request->validate([
            'name'          => 'required|string|max:50|unique:asset_categories,name',
            'document_flow' => 'required|in:serah_terima,logbook',
        ]);

        $category = AssetCategory::create([
            'name'          => $request->name,
            'category_code' => strtoupper(substr($request->name, 0, 1)),
            'document_flow' => $request->document_flow,
        ]);

        $type = $category->types()->create([
            'name'      => $request->name,
            'type_code' => strtoupper(substr($request->name, 0, 3)),
        ]);

        return response()->json(['category' => $category, 'type' => $type]);
    }
    
    public function edit($id)
    {
        $asset = \App\Models\Asset::with(['type', 'partnership'])->findOrFail($id);
        
        return view('pages.it.edit', compact('asset'));
    }

    public function update(Request $request, $id)
    {
        $asset = \App\Models\Asset::findOrFail($id);
        
        $asset->update($request->all());

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui!');
    }

    public function updateQcStatus(Request $request, $id)
    {
        $request->validate([
            'qc_status' => 'required|in:passed,failed',
            'qc_notes'  => 'nullable|string|max:255',
        ]);

        $asset = \App\Models\Asset::findOrFail($id);
        $asset->update([
            'qc_status' => $request->qc_status,
            'qc_date'   => now(),
            'qc_notes'  => $request->qc_notes,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'asset' => $asset]);
        }

        return redirect()->back()->with('success', 'Status QC aset berhasil diperbarui!');
    }
}