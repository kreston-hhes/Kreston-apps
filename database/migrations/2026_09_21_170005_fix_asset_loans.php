<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defensif: cek dulu kolom yang beneran ada sebelum diubah, supaya
     * aman dijalankan berapa kali pun dan tidak error kalau strukturnya
     * ternyata beda dari yang diasumsikan. Tidak menyentuh baris data yang
     * sudah ada — kolom lama (team_division, kalau masih ada) dibiarkan
     * apa adanya, tidak dihapus, biar histori data lama tidak hilang.
     */
    public function up(): void
    {
        Schema::table('asset_loans', function (Blueprint $table) {
            if (Schema::hasColumn('asset_loans', 'anu')) {
                $table->dropColumn('anu');
            }

            if (!Schema::hasColumn('asset_loans', 'partnership_id')) {
                // Nullable dulu (bukan wajib) supaya tidak bentrok sama baris
                // lama yang belum punya partnership_id. Wajib-tidaknya
                // ditegakkan di validasi form/controller saja untuk sekarang.
                $table->foreignId('partnership_id')->nullable()
                    ->after('pic_name')
                    ->constrained('partnerships')->nullOnDelete();
            }

            if (!Schema::hasColumn('asset_loans', 'duration_note')) {
                $table->string('duration_note')->nullable()->after('borrowed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_loans', function (Blueprint $table) {
            if (Schema::hasColumn('asset_loans', 'partnership_id')) {
                $table->dropForeign(['partnership_id']);
                $table->dropColumn('partnership_id');
            }

            if (Schema::hasColumn('asset_loans', 'duration_note')) {
                $table->dropColumn('duration_note');
            }
        });
    }
};