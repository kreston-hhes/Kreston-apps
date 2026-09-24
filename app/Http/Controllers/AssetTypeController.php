<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    /**
     * Menyimpan data Kategori / Tipe Aset Baru dari Modal
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id'   => 'nullable', // Tambahkan validasi ini
            'name'          => 'required|string|max:191',
            'document_flow' => 'required|string',
        ]);

        try {
            AssetType::create([
                'category_id'   => $request->category_id, // Simpan ke DB
                'name'          => $request->name,
                'document_flow' => $request->document_flow,
            ]);

            return redirect()->back()->with('success', 'Kategori aset baru berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambah kategori: ' . $e->getMessage());
        }
    }
}