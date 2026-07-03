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
 Schema::create('asset_assignments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
        $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

        $table->date('assigned_at'); // Tanggal penyerahan laptop
        $table->date('returned_at')->nullable(); // Tanggal pengembalian (diisi jika resign / ganti laptop)

        // --- KOLOM TAMBAHAN BARU ---
        // Menyimpan nama file dokumen yang di-upload (misal: BA_KAPH07260001_20260702.pdf)
        $table->string('signed_document_path')->nullable();

        // Alasan pengembalian/penyerahan (Handover, Resign, Upgrade Device, Broken)
        $table->string('assignment_reason', 50)->default('Handover');

        $table->text('notes')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
