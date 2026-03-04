<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('minggu_mulai');              // Senin awal minggu (misal: 2024-01-08)
            $table->unsignedInteger('max_jam')->default(20);       // Batas maksimal per minggu
            $table->unsignedInteger('jam_terpakai')->default(0);   // Total jam yang sudah dibooked
            $table->timestamps();

            // Satu record per user per minggu
            $table->unique(['user_id', 'minggu_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_limits');
    }
};
