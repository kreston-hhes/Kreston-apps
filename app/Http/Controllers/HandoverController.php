<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf; // composer require barryvdh/laravel-dompdf kalau belum

class HandoverController extends Controller
{
    /**
     * Surat Penyerahan — daftar histori + form
     */
    public function index(Request $request)
    {
        $employees = Employee::where('status', 'active')->get();

        $assets = Asset::whereHas('type', function ($query) {
            $query->where('document_flow', 'serah_terima');
        })
        ->whereIn('placement_status', ['server_room', 'gudang', 'new', 'it_room'])
        ->with('type.category') // <-- TAMBAHKAN INI
        ->get();

        $handovers = AssetAssignment::with(['asset', 'employee'])
            ->where('letter_type', 'handover')
            ->latest('assigned_at')
            ->get();

        $autoOpenAsset = null;
        if ($request->filled('asset_id')) {
            // <-- TAMBAHKAN ->with('type.category') DI SINI JUGA
            $autoOpenAsset = Asset::with('type.category')->find($request->asset_id);

            if ($autoOpenAsset && ! $assets->contains('id', $autoOpenAsset->id)) {
                $assets->prepend($autoOpenAsset);
            }
        }

        return view('pages.it.surat-penyerahan', [
            'title'     => 'Surat Penyerahan',
            'employees' => $employees,
            'assets'    => $assets,
            'handovers' => $handovers,
            'autoOpenAsset' => $autoOpenAsset,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'asset_id'           => 'required|exists:assets,id',
            'assigned_at'        => 'required|date',
            'assignment_reason'  => 'nullable|string|max:255',
            'charger_included'   => 'nullable|boolean',
            'battery_included'   => 'nullable|boolean',
            'bag_included'       => 'nullable|boolean',
        ]);

        // Tambahkan relasi 'type' agar bisa mengambil nama tipenya
        $asset = Asset::with('type')->findOrFail($request->asset_id);

        // --- 🔴 AWAL VALIDASI: CEK JIKA KARYAWAN SUDAH PINJAM TIPE ASET YANG SAMA 🔴 ---
        $hasSameAssetType = AssetAssignment::where('employee_id', $request->employee_id)
            ->whereNull('returned_at') // Memastikan aset masih dipinjam (belum dikembalikan)
            ->where('letter_type', 'handover')
            ->whereHas('asset', function ($query) use ($asset) {
                $query->where('type_id', $asset->type_id); // Cek apakah type_id-nya sama (misal sesama Laptop)
            })->first();

        // Jika ditemukan ada aset dengan tipe yang sama masih dipinjam
        if ($hasSameAssetType) {
            $namaTipe = $asset->type->name ?? 'kategori ini';
            $kodeAsetLama = $hasSameAssetType->asset->hostname ?? $hasSameAssetType->asset->name;
            
            // Lemparkan pesan error ke tampilan depan (Blade)
            return back()->withInput()->withErrors([
                'employee_id' => "Karyawan ini sudah meminjam aset tipe {$namaTipe} ({$kodeAsetLama}). Silakan buat Surat Pengembalian untuk aset tersebut terlebih dahulu sebelum meminjam yang baru."
            ]);
        }
        // --- AKHIR VALIDASI ---

        DB::beginTransaction();
        try {
            $assignment = $asset->assignments()->create([
                'employee_id'       => $request->employee_id,
                'assigned_at'       => $request->assigned_at,
                'letter_type'       => 'handover',
                'letter_number'     => AssetAssignment::generateLetterNumber(),
                'assignment_reason' => $request->assignment_reason ?? 'Handover',
                'charger_included'  => $request->boolean('charger_included'),
                'battery_included'  => $request->boolean('battery_included'),
                'bag_included'      => $request->boolean('bag_included'),
            ]);

            $asset->update(['placement_status' => 'used_by_employee']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('handovers.index')->with('success', 'Penyerahan berhasil disimpan.');
    }

    /**
     * Surat Pengembalian — daftar aset yang lagi dipakai + form checklist
     *
     * PENTING: form checklist ini khusus laptop (field "Fisik laptop",
     * "Tas Laptop", dll), jadi dropdown "Aset yang Dikembalikan" HARUS
     * difilter document_flow=serah_terima. document_flow ada di
     * asset_types, bukan asset_categories — makanya whereHas('type', ...).
     */
    public function returnIndex(Request $request)
    {
        $employees = Employee::where('status', 'active')->get();

        $assets = Asset::whereHas('type', function ($query) {
                $query->where('document_flow', 'serah_terima');
            })
            ->where('placement_status', 'used_by_employee')
            ->with(['currentAssignment.employee', 'type.category'])
            ->get();

        $returns = AssetAssignment::with(['asset', 'employee'])
            ->where('letter_type', 'handover')
            ->whereNotNull('returned_at')
            ->latest('returned_at')
            ->get();

        // Auto-open tab "Buat Surat Pengembalian"
        $autoOpenAsset = null;
        $autoOpenEmployeeId = null;
        if ($request->filled('asset_id')) {
            // UBAH BARIS INI: Tambahkan type.category agar sistem tahu jenis barangnya
            $autoOpenAsset = Asset::with(['currentAssignment.employee', 'type.category'])->find($request->asset_id);

            if ($autoOpenAsset && ! $assets->contains('id', $autoOpenAsset->id)) {
                $assets->prepend($autoOpenAsset);
            }

            $autoOpenEmployeeId = $autoOpenAsset?->currentAssignment?->employee_id;
        }

        return view('pages.it.surat-pengembalian', [
            'title'      => 'Surat Pengembalian',
            'employees'  => $employees,
            'assets'     => $assets,
            'returns'    => $returns,
            'autoOpenAsset'      => $autoOpenAsset,
            'autoOpenEmployeeId' => $autoOpenEmployeeId,
        ]);
    }

    /**
     * Proses pengembalian. Route SENGAJA tidak pakai {assignment} di URL
     * karena form-nya pilih employee+asset lewat dropdown, bukan klik baris
     * riwayat tertentu — assignment yang masih aktif dicari otomatis di sini.
     */
    public function processReturn(Request $request)
    {
        $request->validate([
            'employee_id'              => 'required|exists:employees,id',
            'asset_id'                 => 'required|exists:assets,id',
            'assignment_reason'        => 'required|string|max:50',
            'cleanliness_condition'    => 'required|in:baik,kurang,buruk',
            'physical_condition'       => 'required|in:baik,kurang,buruk',
            'charger_condition'        => 'required|in:baik,kurang,buruk',
            'battery_condition'        => 'required|in:baik,kurang,buruk,tidak_ada',
            'bag_condition'            => 'required|in:baik,kurang,buruk,tidak_ada',
            'notes'                    => 'nullable|string',
            'returned_at'              => 'required|date',
            'delegated_by_employee_id' => 'nullable|exists:employees,id',
        ]);

        $assignment = AssetAssignment::where('employee_id', $request->employee_id)
            ->where('asset_id', $request->asset_id)
            ->whereNull('returned_at')
            ->firstOrFail();

        $assignment->update($request->only([
            'assignment_reason',
            'cleanliness_condition',
            'physical_condition',
            'charger_condition',
            'battery_condition',
            'bag_condition',
            'notes',
            'returned_at',
            'delegated_by_employee_id',
        ]) + [
            'return_letter_number' => AssetAssignment::generateReturnLetterNumber(),
        ]);

        $badConditions = collect($request->only([
            'cleanliness_condition', 'physical_condition', 'charger_condition',
            'battery_condition', 'bag_condition',
        ]))->contains('buruk');

        $assignment->asset->update([
            'placement_status' => 'server_room',
            'condition_status' => $badConditions ? 'damaged' : 'good',
            'condition_notes'  => $request->notes,
        ]);

        return redirect()->route('handovers.return.index')->with('success', 'Pengembalian berhasil dicatat.');
    }

    /**
     * Hapus surat dari tampilan Riwayat (soft delete — row tetap ada di DB,
     * cuma kolom deleted_at keisi, jadi otomatis kefilter dari query biasa).
     */
    public function destroy(AssetAssignment $assignment)
    {
        $assignment->delete();

        return redirect()->route('handovers.index')->with('success', 'Surat berhasil dihapus dari daftar.');
    }

    /**
     * Cetak PDF Surat Penyerahan (butuh package barryvdh/laravel-dompdf)
     */
    public function printPenyerahan(Request $request)
    {
        $employee = Employee::findOrFail($request->employee_id);
        $asset = Asset::findOrFail($request->asset_id);

        $assignment = AssetAssignment::where('employee_id', $employee->id)
            ->where('asset_id', $asset->id)
            ->where('letter_type', 'handover')
            ->whereNull('returned_at')
            ->latest('assigned_at')
            ->first();

        $documentNumber = $assignment?->letter_number ?? AssetAssignment::generateLetterNumber();

        $pdf = Pdf::loadView('pages.it.pdf.surat-penyerahan', [
            'employee'        => $employee,
            'asset'           => $asset,
            'document_number' => $documentNumber,
        ]);

        return $pdf->stream('surat-penyerahan-' . $asset->asset_code . '.pdf');
    }

    /**
     * Cetak PDF Surat Pengembalian
     */
    public function printPengembalian(Request $request)
    {
        $employee = Employee::findOrFail($request->employee_id);
        $asset = Asset::findOrFail($request->asset_id);

        $assignment = AssetAssignment::where('employee_id', $employee->id)
            ->where('asset_id', $asset->id)
            ->whereNotNull('returned_at')
            ->latest('returned_at')
            ->first();

        if ($assignment) {
            $kondisi = [
                'kebersihan' => $assignment->cleanliness_condition,
                'fisik'      => $assignment->physical_condition,
                'charger'    => $assignment->charger_condition,
                'baterai'    => $assignment->battery_condition,
                'tas'        => $assignment->bag_condition,
                'lainnya'    => $assignment->notes,
            ];
            $documentNumber = $assignment->return_letter_number;
        } else {
            $kondisi = $request->only(['kebersihan', 'fisik', 'charger', 'baterai', 'tas', 'lainnya']);
            $documentNumber = AssetAssignment::generateReturnLetterNumber();
        }

        $pdf = Pdf::loadView('pages.it.pdf.surat-pengembalian', [
            'employee'        => $employee,
            'asset'           => $asset,
            'kondisi'         => $kondisi,
            'document_number' => $documentNumber,
        ]);

        return $pdf->stream('surat-pengembalian-' . $asset->asset_code . '.pdf');
    }
}