<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets');
            $table->string('pic_name'); // Nama PIC yang minjem
            $table->foreignId('partnership_id')->nullable()->constrained('partnerships'); // Partner/tim
            $table->string('duration_note')->nullable(); // Estimasi durasi, teks bebas ("2 hari")
            $table->timestamp('borrowed_at');
            $table->timestamp('returned_at')->nullable(); // null = masih dipinjam
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_loans');
    }
};