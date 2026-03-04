<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'hololens_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // ─── Relasi ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hololens(): BelongsTo
    {
        return $this->belongsTo(Hololens::class);
    }

    // ─── Scope ────────────────────────────────────────────

    /** Hanya booking yang masih aktif (belum dibatalkan) */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /** Booking minggu ini */
    public function scopeMingguIni($query)
    {
        $senin = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $minggu = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        return $query->whereBetween('tanggal', [$senin, $minggu]);
    }

    /** Booking pada tanggal tertentu */
    public function scopePadaTanggal($query, string $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }
}
