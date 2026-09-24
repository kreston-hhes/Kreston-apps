<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            // Nomor surat pengembalian terpisah dari letter_number (yang isinya
            // nomor surat PENYERAHAN) — satu row assignment bisa punya keduanya,
            // karena pengembalian nge-update row assignment yang sama, bukan bikin baru.
            $table->string('return_letter_number')->nullable()->after('letter_number');
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropColumn('return_letter_number');
        });
    }
};