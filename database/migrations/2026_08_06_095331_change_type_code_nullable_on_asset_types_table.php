<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asset_types', function (Blueprint $table) {
            // Menambahkan perintah ->nullable()->change() untuk mengubah kolom yang sudah ada
            $table->string('type_code')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('asset_types', function (Blueprint $table) {
            // Mengembalikan ke pengaturan awal (wajib diisi) jika migration di-rollback
            $table->string('type_code')->nullable(false)->change();
        });
    }
};