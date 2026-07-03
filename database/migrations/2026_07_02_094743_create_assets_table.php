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
   Schema::create('assets', function (Blueprint $table) {
        $table->id();
        // Menghubungkan ke tabel partnerships database Anda
        $table->foreignId('partnership_id')->constrained('partnerships')->onDelete('cascade');
        // Menghubungkan ke jenis aset
        $table->foreignId('type_id')->constrained('asset_types')->onDelete('cascade');

        // Hostname / Kode Aset (Panjang 15 karakter, tanpa simbol, unik)
        $table->string('asset_code', 15)->unique();

        $table->string('name'); // Nama/Model Perangkat (ex: Lenovo ThinkPad X1)
        $table->string('serial_number')->nullable(); // Nullable untuk software
        $table->text('specification'); // Spesifikasi detail
        $table->date('purchase_date'); // Tanggal pembelian (untuk ambil bulan/tahun kode)
        $table->date('warranty_expired')->nullable();

        // Status Kondisi & Penempatan
        $table->enum('condition_status', ['good', 'damaged', 'maintenance', 'disposed'])->default('good');
        $table->enum('placement_status', ['it_room', 'used_by_employee'])->default('it_room');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
