<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrinterBilling;
use App\Models\Asset;

class PrinterBillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m')) . '-01';
        $billings = PrinterBilling::with('asset.partnership')
            ->where('billing_month', $month)
            ->get(); 
        $printers = Asset::whereHas('type.category', function ($query) {
            $query->where('name', 'Printer');
        })->get();
        return view('pages.it.printer-billings', [
            'title'    => 'Billing Bulanan Printer',
            'billings' => $billings,
            'printers' => $printers,
            'month'    => $month,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id'      => 'required|exists:assets,id',
            'billing_month' => 'required|date',
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes'         => 'nullable|string',
        ]);

        $path = $request->file('file')->store('printer-billings', 'public');

        PrinterBilling::updateOrCreate(
            [
                'asset_id'      => $request->asset_id,
                'billing_month' => date('Y-m-01', strtotime($request->billing_month)),
            ],
            [
                'file_path' => $path,
                'notes'     => $request->notes,
            ]
        );

        return redirect()->route('printer-billings.index')->with('success', 'Billing berhasil diupload.');
    }
}