<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('usage_limits', function (Blueprint $table) {
        $table->unsignedInteger('max_jam_harian')->default(5); // ← tambah ini
    });
}

public function down(): void
{
    Schema::table('usage_limits', function (Blueprint $table) {
        $table->dropColumn('max_jam_harian'); // ← tambah ini
    });
}
};
