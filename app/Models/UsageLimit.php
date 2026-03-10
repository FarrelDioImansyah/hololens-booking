<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLimit extends Model
{
    protected $fillable = [
        'user_id',
        'minggu_mulai',
        'max_jam',
        'max_jam_harian',
        'jam_terpakai',
    ];

    protected $casts = [
        'minggu_mulai' => 'date',
    ];

    // ─── Relasi ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helper ───────────────────────────────────────────

    public function sisaJam(): int
    {
        return max(0, $this->max_jam - $this->jam_terpakai);
    }

    public function masihBisaBooking(): bool
    {
        return $this->jam_terpakai < $this->max_jam;
    }

    /** Persentase penggunaan (untuk progress bar) */
    public function persenTerpakai(): int
    {
        if ($this->max_jam === 0) return 100;
        return (int) min(100, ($this->jam_terpakai / $this->max_jam) * 100);
    }
}
