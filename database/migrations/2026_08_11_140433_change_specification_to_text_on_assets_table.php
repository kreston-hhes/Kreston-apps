<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Mengubah tipe kolom menjadi text
            $table->text('specification')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Mengembalikan ke varchar/string jika di-rollback
            $table->string('specification', 255)->nullable()->change();
        });
    }
};