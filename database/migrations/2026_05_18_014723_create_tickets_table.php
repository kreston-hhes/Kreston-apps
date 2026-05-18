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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('id_ticket')->unique();
            $table->date('request_date');
            $table->foreignId('id_employee')->constrained('employees')->onDelete('cascade');
            $table->text('issue_description');
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->string('assigned_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
