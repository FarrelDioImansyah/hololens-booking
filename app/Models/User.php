<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class User extends Authenticatable
{
    protected $fillable = [
        'nama_kelompok',
        'username',
        'password',
        'role',
    ];

    protected $hidden = ['password'];

    // ─── Relasi ───────────────────────────────────────────

    /** Satu user bisa punya banyak booking */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Satu user punya banyak record limit (satu per minggu) */
    public function usageLimits(): HasMany
    {
        return $this->hasMany(UsageLimit::class);
    }

    // ─── Helper Methods ───────────────────────────────────

    /** Ambil atau buat record usage limit untuk minggu ini */
    public function getOrCreateWeeklyLimit(): UsageLimit
    {
        $senin = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

        return UsageLimit::firstOrCreate(
            ['user_id' => $this->id, 'minggu_mulai' => $senin],
            ['max_jam' => 20, 'jam_terpakai' => 0]
        );
    }

    /** Sisa jam minggu ini */
    public function sisaJamMingguIni(): int
    {
        $limit = $this->getOrCreateWeeklyLimit();
        return max(0, $limit->max_jam - $limit->jam_terpakai);
    }

    /** Apakah user ini admin? */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
