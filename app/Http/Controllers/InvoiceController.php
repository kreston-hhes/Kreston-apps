<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Asset;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('asset.partnership')->latest('invoice_date')->get();

        return view('pages.it.invoices', [
            'title'    => 'Invoice',
            'invoices' => $invoices,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'asset_id'     => 'required|exists:assets,id',
            'invoice_date' => 'nullable|date',
            'file'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes'        => 'nullable|string',
        ]);

        $path = $request->file('file')->store('invoices', 'public');

        Invoice::create([
            'asset_id'     => $request->asset_id,
            'invoice_date' => $request->invoice_date,
            'file_path'    => $path,
            'notes'        => $request->notes,
        ]);

        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil diupload.');
    }
}
