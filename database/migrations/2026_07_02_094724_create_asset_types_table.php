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
       Schema::create('asset_types', function (Blueprint $table) {
        $table->id();
        // Relasi ke kategori aset
        $table->foreignId('category_id')->constrained('asset_categories')->onDelete('cascade');
        $table->string('name', 50); // Contoh: Laptop, PC Desktop, Router
        $table->char('type_code', 3); // Contoh: LPT, PCX, RTR
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_types');
    }
};
