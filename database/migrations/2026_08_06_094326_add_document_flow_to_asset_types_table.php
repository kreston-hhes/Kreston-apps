<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::table('asset_types', function (Blueprint $table) {
            $table->string('document_flow')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('asset_types', function (Blueprint $table) {
        $table->dropColumn('document_flow');
    });
}
};
