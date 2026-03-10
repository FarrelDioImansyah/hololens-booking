<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Booking;
use App\Models\Hololens;
use App\Models\UsageLimit;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ─── Dashboard Admin ──────────────────────────────────
    public function dashboard()
    {
        $totalBooking      = Booking::aktif()->count();
        $bookingHariIni    = Booking::aktif()->padaTanggal(Carbon::today())->count();
        $totalKelompok     = User::where('role', 'kelompok')->count();
        $bookingMingguIni  = Booking::aktif()->mingguIni()->count();

        // Booking per kelompok minggu ini (untuk grafik/tabel)
        $statsKelompok = User::where('role', 'kelompok')
            ->with(['bookings' => fn($q) => $q->aktif()->mingguIni()])
            ->get()
            ->map(fn($u) => [
                'nama'   => $u->nama_kelompok,
                'jumlah' => $u->bookings->count(),
            ]);

        return view('admin.dashboard', compact(
            'totalBooking', 'bookingHariIni', 'totalKelompok',
            'bookingMingguIni', 'statsKelompok'
        ));
    }

    // ─── Lihat semua booking ──────────────────────────────
    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'hololens'])->orderByDesc('tanggal')->orderByDesc('jam_mulai');

        // Filter opsional
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings  = $query->paginate(20)->withQueryString();
        $kelompoks = User::where('role', 'kelompok')->get();

        return view('admin.bookings', compact('bookings', 'kelompoks'));
    }

    // ─── Hapus booking (admin force delete) ──────────────
    public function destroyBooking($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status === 'aktif') {
            // Kembalikan kuota ke pemilik booking
            $limit = User::find($booking->user_id)?->getOrCreateWeeklyLimit();
            if ($limit && $limit->jam_terpakai > 0) {
                $limit->decrement('jam_terpakai');
            }
        }

        $booking->update(['status' => 'dibatalkan']);

        return back()->with('success', 'Booking berhasil dihapus.');
    }

    // ─── Kelola akun kelompok ─────────────────────────────
    public function users()
    {
        $users = User::where('role', 'kelompok')
            ->with(['usageLimits' => fn($q) => $q->where('minggu_mulai', Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString())])
            ->get();

        return view('admin.users', compact('users'));
    }

    // ─── Tambah akun kelompok ─────────────────────────────
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama_kelompok' => 'required|string|max:100',
            'username'      => 'required|string|unique:users,username|max:50',
            'password'      => 'required|string|min:6',
        ]);

        User::create([
            'nama_kelompok' => $request->nama_kelompok,
            'username'      => $request->username,
            'password'      => Hash::make($request->password),
            'role'          => 'kelompok',
        ]);

        return back()->with('success', "Akun '{$request->username}' berhasil ditambahkan.");
    }

    // ─── Atur batas jam kelompok ──────────────────────────
    public function setLimit(Request $request, $userId)
    {
        $request->validate([
            'max_jam' => 'required|integer|min:1|max:100',
        ]);

        $senin = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

        UsageLimit::updateOrCreate(
            ['user_id' => $userId, 'minggu_mulai' => $senin],
            ['max_jam' => $request->max_jam]
        );

        return back()->with('success', 'Batas jam berhasil diperbarui.');
    }

    // ─── Reset jam semua kelompok (manual) ───────────────
    public function resetAllLimits()
    {
        $senin = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

        UsageLimit::where('minggu_mulai', $senin)->update(['jam_terpakai' => 0]);

        return back()->with('success', 'Semua jam penggunaan berhasil direset.');
    }
    public function setLimitHarian(Request $request, $userId)
{
    $request->validate([
        'max_jam_harian' => 'required|integer|min:1|max:20',
    ]);

    $senin = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

    // Update semua record yang ada
    UsageLimit::where('user_id', $userId)
        ->update(['max_jam_harian' => $request->max_jam_harian]);

    // Pastikan minggu ini juga ada recordnya
    UsageLimit::firstOrCreate(
        ['user_id' => $userId, 'minggu_mulai' => $senin],
        ['max_jam_harian' => $request->max_jam_harian, 'max_jam' => 20]
    );

    return back()->with('success', 'Limit harian berhasil diperbarui.');

}
}
