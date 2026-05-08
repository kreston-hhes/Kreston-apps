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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date')->nullable();
            $table->string('position');
            $table->string('division');
            $table->date('date_of_entry');
            $table->date('release_date')->nullable();

           // Foreign Key ke Partnerships
            $table->foreignId('partnership_id')->constrained('partnerships')->onDelete('cascade');

            // Foreign Key ke Manager (mengarah ke id di tabel yang sama)
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');

            // connect to users table
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->enum('status', ['active', 'inactive'])->default('active');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
