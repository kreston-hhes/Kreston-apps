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
        Schema::create('printer_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');

            // Bulan billing, disimpan sebagai tanggal 1 di bulan tsb
            // (contoh: 2026-07-01 untuk billing Juli 2026)
            $table->date('billing_month');

            $table->string('file_path');
            $table->text('notes')->nullable();

            $table->timestamps();

            // Satu printer cuma boleh punya satu file billing per bulan
            $table->unique(['asset_id', 'billing_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_billings');
    }
};
