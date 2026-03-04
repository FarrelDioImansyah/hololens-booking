<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Daftarkan semua artisan command custom di sini.
     */
    protected $commands = [
        Commands\ResetWeeklyLimits::class,
    ];

    /**
     * Jadwalkan command yang berjalan otomatis.
     *
     * Aktifkan cron di server:
     *   * * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1
     */
    protected function schedule(Schedule $schedule): void
    {
        // Reset jam setiap Senin pukul 00:00
        $schedule->command('booking:reset-weekly --force')
                 ->weeklyOn(1, '00:00')  // 1 = Senin
                 ->appendOutputTo(storage_path('logs/scheduler.log'));
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
