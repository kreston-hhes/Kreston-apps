<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            // 'serah_terima' = laptop karyawan -> Surat Penyerahan/Pengembalian
            // 'skppi'        = aset IT kecil lain -> SKPPI
            // 'logbook'      = aset dipakai bersama (proyektor) -> Logbook Peminjaman
            $table->enum('document_flow', ['serah_terima', 'skppi', 'logbook'])
                ->default('serah_terima')
                ->after('category_code');

            // Daftar nama field default spesifikasi untuk kategori ini,
            // dipakai buat prefill form tambah aset (contoh Laptop:
            // ["Merk","Tipe","Processor","RAM",...]). Admin tetap bebas
            // hapus/tambah field manual saat mengisi form.
            $table->json('spec_template')->nullable()->after('document_flow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn(['document_flow', 'spec_template']);
        });
    }
};
