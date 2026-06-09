<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // otomatis menambahkan 'resigned' dan 'deleted' ke database siapa pun yang menjalankannya
        DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('active', 'inactive', 'resigned', 'deleted') DEFAULT 'active'");
    }

    public function down(): void
    {
        // command mengembalikan ke versi lama jika terjadi rollback
        DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    }
};