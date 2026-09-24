<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TestController extends Controller
{
    public function index()
    {
        return view('pages.test-form', [
            'title' => 'Test Form',
        ]);
    }
    public function cetakPdf()
    {
        // Data yang akan dikirim ke view
        $data = [
            'title' => 'Invoice Pembelian',
            'date' => date('d/m/Y')
        ];

        // Load view dan datanya
        $pdf = Pdf::loadView('test', $data);

        // Pilih salah satu:
        //return $pdf->download('invoice.pdf'); // Langsung download
        return $pdf->stream(); // Tampilkan di tab browser (preview)
    }
}
