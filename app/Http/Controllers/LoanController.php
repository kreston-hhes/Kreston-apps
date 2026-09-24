<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\Partnership;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * Logbook Peminjaman Proyektor — daftar histori + form
     */
    public function index()
    {
        $partnerships = Partnership::where('status', 'active')->orderBy('name')->get();

        // 1. Ambil Aset Proyektor
        $assets = Asset::whereHas('type.category', function ($query) {
                $query->where('document_flow', 'logbook');
            })
            ->orderBy('name')
            ->get();

        // 2. Cek status ketersediaan masing-masing proyektor
        foreach ($assets as $asset) {
            // Jika tidak ada data peminjaman yang 'returned_at'-nya NULL, berarti tersedia (true)
            $asset->is_available = !AssetLoan::where('asset_id', $asset->id)
                ->whereNull('returned_at')
                ->exists();
        }

        $loans = AssetLoan::with(['asset', 'partnership'])
            ->latest('borrowed_at')
            ->get();

        return view('pages.it.logbook-peminjaman', [
            'title'        => 'Logbook Peminjaman',
            'partnerships' => $partnerships,
            'assets'       => $assets,
            'loans'        => $loans,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pic_name'        => 'required|string|max:191',
            'partnership_id'  => 'required|exists:partnerships,id',
            'asset_id'        => 'required|exists:assets,id',
            'duration_note'   => 'required|string|max:191',
        ]);

        // Ambil data nama partnership yang dipilih user
        $partner = \App\Models\Partnership::find($request->partnership_id);

        AssetLoan::create([
            'asset_id'       => $request->asset_id,
            'pic_name'       => $request->pic_name,
            'partnership_id' => $request->partnership_id,
            'duration_note'  => $request->duration_note,
            'borrowed_at'    => now(),
            // Salin otomatis nama partnership ke team_division agar database tidak ngambek
            'team_division'  => $partner ? $partner->name : '-', 
        ]);

        return redirect()->route('loans.index')->with('success', 'Peminjaman berhasil dicatat.');
    }

    /**
     * Tandai peminjaman selesai (proyektor dikembalikan)
     */
    public function finish(AssetLoan $loan)
    {
        $loan->update(['returned_at' => now()]);

        return redirect()->route('loans.index')->with('success', 'Peminjaman ditandai selesai.');
    }
}