<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\AssetLoan;

class ArchiveController extends Controller
{
    /**
     * Arsip Surat — 4 tab dalam satu halaman, semua data ditarik dari
     * asset_assignments dan asset_loans yang sudah ada, tidak ada tabel
     * arsip terpisah.
     */
    public function index()
    {
        $handovers = AssetAssignment::with(['asset', 'employee'])
            ->where('letter_type', 'handover')
            ->latest('assigned_at')
            ->get();

        $returns = AssetAssignment::with(['asset', 'employee'])
            ->where('letter_type', 'handover')
            ->whereNotNull('returned_at')
            ->latest('returned_at')
            ->get();

        $skppi = AssetAssignment::with(['asset', 'employee'])
            ->where('letter_type', 'skppi')
            ->latest('assigned_at')
            ->get();

        $logbook = AssetLoan::with('asset')
            ->latest('borrowed_at')
            ->get();

        return view('pages.it.arsip-surat', [
            'title'     => 'Arsip Surat',
            'handovers' => $handovers,
            'returns'   => $returns,
            'skppi'     => $skppi,
            'logbook'   => $logbook,
        ]);
    }
}