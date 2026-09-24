<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            // Kita perbesar kapasitasnya jadi 50 karakter agar aman
            $table->string('asset_code', 50)->change();
        });
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            // Kembalikan ke 15 jika di-rollback
            $table->string('asset_code', 15)->change();
        });
    }
};