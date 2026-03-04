<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UsageLimit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ResetWeeklyLimits extends Command
{
    /**
     * Nama dan signature command.
     * Jalankan manual: php artisan booking:reset-weekly
     */
    protected $signature = 'booking:reset-weekly
                            {--force : Paksa reset meskipun bukan hari Senin}';

    protected $description = 'Reset jam_terpakai semua kelompok setiap awal minggu (Senin 00:00)';

    public function handle(): int
    {
        $hariIni = Carbon::now()->dayOfWeek;

        // Jika bukan Senin dan tidak pakai --force, batalkan
        if ($hariIni !== Carbon::MONDAY && !$this->option('force')) {
            $this->warn('Hari ini bukan Senin. Gunakan --force untuk memaksa reset.');
            return self::FAILURE;
        }

        $senin = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

        $this->info("Memulai reset limit minggu: $senin ...");

        // Ambil semua kelompok
        $kelompoks = User::where('role', 'kelompok')->get();
        $jumlah = 0;

        foreach ($kelompoks as $kelompok) {
            // Update atau buat record untuk minggu baru
            UsageLimit::updateOrCreate(
                ['user_id' => $kelompok->id, 'minggu_mulai' => $senin],
                ['jam_terpakai' => 0, 'max_jam' => 20]
            );
            $jumlah++;
        }

        $this->info("✅ Reset selesai. $jumlah kelompok direset.");

        // Catat ke log aplikasi
        Log::info("ResetWeeklyLimits: $jumlah kelompok direset untuk minggu $senin");

        return self::SUCCESS;
    }
}
