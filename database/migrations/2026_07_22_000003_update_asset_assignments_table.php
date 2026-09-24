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
        Schema::table('asset_assignments', function (Blueprint $table) {
            // 1. Menentukan template surat mana yang dicetak: laptop
            //    (handover) atau aset IT kecil lain (skppi)
            $table->enum('letter_type', ['handover', 'skppi'])
                ->default('handover')->after('assignment_reason');

            // 2. Nomor surat tercetak, contoh: 011/ITS/SKPPI/04/2025
            $table->string('letter_number')->nullable()->unique()->after('letter_type');

            // 3. Checklist kelengkapan saat serah terima (bisa beda tiap
            //    kali laptop pindah tangan, jadi disimpan per kejadian)
            $table->boolean('charger_included')->nullable()->after('letter_number');
            $table->boolean('battery_included')->nullable()->after('charger_included');
            $table->boolean('bag_included')->nullable()->after('battery_included');

            // 4. Checklist kondisi saat pengembalian
            $table->enum('cleanliness_condition', ['baik', 'kurang', 'buruk'])->nullable()->after('bag_included');
            $table->enum('physical_condition', ['baik', 'kurang', 'buruk'])->nullable()->after('cleanliness_condition');
            $table->enum('charger_condition', ['baik', 'kurang', 'buruk'])->nullable()->after('physical_condition');
            $table->enum('battery_condition', ['baik', 'kurang', 'buruk'])->nullable()->after('charger_condition');
            $table->enum('bag_condition', ['baik', 'kurang', 'buruk'])->nullable()->after('battery_condition');

            // 5. Kalau pengembalian diwakilkan orang lain
            $table->foreignId('delegated_by_employee_id')->nullable()
                ->after('bag_condition')
                ->constrained('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['delegated_by_employee_id']);
            $table->dropColumn([
                'letter_type',
                'letter_number',
                'charger_included',
                'battery_included',
                'bag_included',
                'cleanliness_condition',
                'physical_condition',
                'charger_condition',
                'battery_condition',
                'bag_condition',
                'delegated_by_employee_id',
            ]);
        });
    }
};
