<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = User::findOrFail(session('user_id'));
        $limit = $user->getOrCreateWeeklyLimit();

        // Booking aktif milik kelompok ini minggu berjalan
        $bookingMingguIni = Booking::aktif()
            ->mingguIni()
            ->where('user_id', $user->id)
            ->with('hololens')
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        // Booking mendatang (hari ini dan seterusnya)
        $bookingMendatang = Booking::aktif()
            ->where('user_id', $user->id)
            ->where('tanggal', '>=', Carbon::today())
            ->with('hololens')
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->take(5)
            ->get();

        // Booking hari ini
        $bookingHariIni = Booking::aktif()
            ->where('user_id', $user->id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->count();
            $maxHarian = $limit->max_jam_harian ?? 5;
        return view('dashboard.index', compact('user', 'limit', 'bookingMingguIni', 'bookingMendatang', 'bookingHariIni','maxHarian' ));
    }
}
