<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hololens extends Model
{
    protected $table = 'hololens';

    protected $fillable = ['nama_alat', 'status'];

    // ─── Relasi ───────────────────────────────────────────

    /** Satu HoloLens bisa di-booking banyak kali (di slot berbeda) */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ─── Scope ────────────────────────────────────────────

    /** Hanya HoloLens yang berstatus aktif */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
