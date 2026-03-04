<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hololens_id')->constrained('hololens')->onDelete('cascade');
            $table->date('tanggal');                   // Tanggal booking
            $table->time('jam_mulai');                 // Contoh: 08:00:00
            $table->time('jam_selesai');               // Contoh: 09:00:00 (otomatis +1 jam)
            $table->enum('status', ['aktif', 'dibatalkan'])->default('aktif');
            $table->timestamps();

            // Mencegah double booking: kombinasi hololens + tanggal + jam harus unik
            $table->unique(['hololens_id', 'tanggal', 'jam_mulai'], 'unique_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
