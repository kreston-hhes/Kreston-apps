<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consumable;
use App\Models\Asset;

class ConsumableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $consumables = Consumable::with('asset.partnership')->get();

        // Dropdown pilihan printer buat form tambah item consumable
        $printers = Asset::whereHas('type.category', function ($query) {
            $query->where('name', 'Printer');
        })->get();

        return view('pages.it.consumables', [
            'title'       => 'Stok Consumable',
            'consumables' => $consumables,
            'printers'    => $printers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_id'      => 'required|exists:assets,id',
            'name'          => 'required|string|max:191',
            'type'          => 'required|in:toner,drum,other',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        Consumable::create($request->only([
            'asset_id', 'name', 'type', 'current_stock', 'minimum_stock', 'unit',
        ]));

        return redirect()->route('consumables.index')->with('success', 'Item consumable berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consumable $consumable)
    {
        $request->validate([
            'current_stock' => 'required|integer|min:0',
        ]);

        $consumable->update(['current_stock' => $request->current_stock]);

        return redirect()->route('consumables.index')->with('success', 'Stok berhasil diupdate.');
    }
}
