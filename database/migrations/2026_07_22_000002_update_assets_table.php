<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. specification: text -> json (pastikan data lama sudah JSON
        //    valid sebelum migration ini dijalankan)
        Schema::table('assets', function (Blueprint $table) {
            $table->json('specification')->nullable()->change();
        });

        // 2. placement_status: tidak ada lagi 'it_room' terpisah — laptop
        //    baru yang belum dialokasikan dan laptop bekas sama-sama fisik
        //    berada di ruang server, jadi 'it_room' di-rename jadi
        //    'server_room'.
        DB::statement("ALTER TABLE assets MODIFY COLUMN placement_status
            ENUM('it_room', 'server_room', 'used_by_employee')
            NOT NULL DEFAULT 'it_room'");

        DB::table('assets')
            ->where('placement_status', 'it_room')
            ->update(['placement_status' => 'server_room']);

        DB::statement("ALTER TABLE assets MODIFY COLUMN placement_status
            ENUM('server_room', 'used_by_employee')
            NOT NULL DEFAULT 'server_room'");

        Schema::table('assets', function (Blueprint $table) {
            // 3. Catatan kondisi bebas teks, terpisah dari condition_status
            //    enum, buat histori detail (contoh: part yang dipindah ke
            //    laptop lain).
            $table->text('condition_notes')->nullable()->after('condition_status');

            // 4. Tracking QC sederhana khusus inventori laptop baru masuk.
            //    qc_status null = belum perlu QC (aset lama / non-laptop).
            $table->enum('qc_status', ['pending', 'passed', 'failed'])
                ->nullable()->after('condition_notes');
            $table->date('qc_date')->nullable()->after('qc_status');
            $table->text('qc_notes')->nullable()->after('qc_date');

            // 5. Aset umum (contoh: printer bersama) tidak dimiliki tim
            //    tertentu. Kosong = "Umum" saat ditampilkan.
            $table->foreignId('partnership_id')->nullable()->change();

            // 6. Info pembelian dari wireframe form tambah aset.
            $table->string('vendor')->nullable()->after('warranty_expired');
            $table->string('purchased_by')->nullable()->after('vendor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'condition_notes',
                'qc_status',
                'qc_date',
                'qc_notes',
                'vendor',
                'purchased_by',
            ]);
            $table->foreignId('partnership_id')->nullable(false)->change();
        });

        DB::statement("ALTER TABLE assets MODIFY COLUMN placement_status
            ENUM('it_room', 'server_room', 'used_by_employee')
            NOT NULL DEFAULT 'server_room'");

        DB::table('assets')
            ->where('placement_status', 'server_room')
            ->update(['placement_status' => 'it_room']);

        DB::statement("ALTER TABLE assets MODIFY COLUMN placement_status
            ENUM('it_room', 'used_by_employee')
            NOT NULL DEFAULT 'it_room'");

        Schema::table('assets', function (Blueprint $table) {
            $table->text('specification')->nullable(false)->change();
        });
    }
};
