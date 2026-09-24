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
        Schema::create('consumables', function (Blueprint $table) {
            $table->id();

            // Printer pemilik consumable ini. Satu printer bisa punya
            // beberapa baris consumable (contoh: printer CMYK punya 4 toner
            // warna berbeda, masing-masing baris sendiri).
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');

            $table->string('name'); // Contoh: Toner Cyan, Drum Unit
            $table->enum('type', ['toner', 'drum', 'other'])->default('toner');
            $table->unsignedInteger('current_stock')->default(0);
            $table->unsignedInteger('minimum_stock')->default(1);
            $table->string('unit', 20)->default('pcs');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumables');
    }
};
