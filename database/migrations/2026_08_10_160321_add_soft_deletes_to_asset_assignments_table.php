<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            // kolom deleted_at: kalau ada isinya berarti "dihapus" (hidden dari
            // tampilan), tapi row-nya tetap utuh di database
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};