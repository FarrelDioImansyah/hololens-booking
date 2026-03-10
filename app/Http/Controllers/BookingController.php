<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\Hololens;
use App\Models\User;
use Carbon\Carbon;

class BookingController extends Controller
{
    // ─── Konstanta jam operasional ────────────────────────
    const JAM_MULAI  = 8;   // 08:00
    const JAM_SELESAI = 21; // 21:00 (slot terakhir mulai 20:00)

    // ─── Halaman jadwal booking ───────────────────────────
    public function index(Request $request)
    {
        $user = User::findOrFail(session('user_id'));

        // Tanggal yang dipilih (default: hari ini)
        $tanggal = $request->get('tanggal', Carbon::today()->toDateString());
        $tanggalCarbon = Carbon::parse($tanggal);

        // Semua HoloLens aktif
        $hololensList = Hololens::aktif()->get();

        // Ambil semua booking AKTIF pada tanggal tersebut
        $bookings = Booking::aktif()
            ->padaTanggal($tanggal)
            ->with(['user', 'hololens'])
            ->get()
            ->groupBy(fn($b) => $b->hololens_id . '_' . $b->jam_mulai);
            // Key: "hololens_id_jam_mulai" → misal "1_08:00:00"

        // Buat slot jam: 08:00 sampai 20:00 (13 slot)
        $slots = [];
        for ($jam = self::JAM_MULAI; $jam < self::JAM_SELESAI; $jam++) {
            $slots[] = sprintf('%02d:00', $jam);
        }

        // Info limit minggu ini
        $limit = $user->getOrCreateWeeklyLimit();

        return view('schedule.index', compact(
            'tanggal', 'tanggalCarbon', 'hololensList', 'bookings', 'slots', 'limit', 'user'
        ));
    }

    // ─── Simpan booking baru ──────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'hololens_id' => 'required|exists:hololens,id',
            'tanggal'     => 'required|date|after_or_equal:today',
            'jam_mulai'   => 'required|date_format:H:i',
        ], [
            'tanggal.after_or_equal' => 'Tidak bisa booking tanggal yang sudah lewat.',
        ]);

        $user      = User::findOrFail(session('user_id'));
        $jamMulai  = $request->jam_mulai . ':00';
        $jamSelesai = sprintf('%02d:00:00', (int)substr($request->jam_mulai, 0, 2) + 1);

        // Validasi jam operasional
        $jamInt = (int)substr($request->jam_mulai, 0, 2);
        if ($jamInt < self::JAM_MULAI || $jamInt >= self::JAM_SELESAI) {
            return response()->json([
                'success' => false,
                'message' => 'Jam harus antara 08:00 – 20:00.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $user, $jamMulai, $jamSelesai) {

                // ── CEK 1: Apakah slot masih kosong? ──────
                $slotTerisi = Booking::aktif()
                    ->where('hololens_id', $request->hololens_id)
                    ->where('tanggal', $request->tanggal)
                    ->where('jam_mulai', $jamMulai)
                    ->lockForUpdate()  // Lock row agar tidak race condition
                    ->exists();

                if ($slotTerisi) {
                    throw new \Exception('Slot ini sudah dibooking kelompok lain. Pilih slot lain.');
                }
                 // ── CEK 2: Tidak boleh booking 2 HoloLens di jam yang sama ──
                $doubleHololens = Booking::aktif()
                    ->where('user_id', $user->id)
                    ->where('tanggal', $request->tanggal)
                    ->where('jam_mulai', $jamMulai)
                    ->exists();

                if ($doubleHololens) {
                    throw new \Exception('Kamu sudah booking HoloLens lain di jam yang sama!');
                }

                // ── CEK 3: Maksimal 5 jam per hari ────────────
                $bookingHariIni = Booking::aktif()
                    ->where('user_id', $user->id)
                    ->where('tanggal', $request->tanggal)
                    ->count();

                if ($bookingHariIni >= 5) {
                    throw new \Exception('Batas maksimal 5 jam per hari sudah tercapai!');
                }
                // ── CEK 4: Apakah kelompok masih punya kuota? ──
                $limit = $user->getOrCreateWeeklyLimit();

                if (!$limit->masihBisaBooking()) {
                    throw new \Exception(
                        "Kuota kamu sudah habis minggu ini ({$limit->jam_terpakai}/{$limit->max_jam} jam). " .
                        "Tunggu reset hari Senin depan."
                    );
                }

                // ── SIMPAN BOOKING ─────────────────────────
                Booking::create([
                    'user_id'     => $user->id,
                    'hololens_id' => $request->hololens_id,
                    'tanggal'     => $request->tanggal,
                    'jam_mulai'   => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'status'      => 'aktif',
                ]);

                // ── UPDATE JAM TERPAKAI ────────────────────
                $limit->increment('jam_terpakai');
            });

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil! Selamat menggunakan HoloLens.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ─── Batalkan booking ─────────────────────────────────
    public function destroy($id)
    {
        $user    = User::findOrFail(session('user_id'));
        $booking = Booking::findOrFail($id);

        // Kelompok hanya bisa batalkan booking milik sendiri
        if ($booking->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak berhak membatalkan booking ini.',
            ], 403);
        }

        if ($booking->status === 'dibatalkan') {
            return response()->json([
                'success' => false,
                'message' => 'Booking sudah dibatalkan sebelumnya.',
            ], 422);
        }

        DB::transaction(function () use ($booking, $user) {
            // Tandai booking sebagai dibatalkan
            $booking->update(['status' => 'dibatalkan']);

            // Kembalikan 1 jam ke kuota kelompok yang punya booking
            $targetUser = $user->isAdmin() ? User::find($booking->user_id) : $user;
            $limit = $targetUser->getOrCreateWeeklyLimit();

            if ($limit->jam_terpakai > 0) {
                $limit->decrement('jam_terpakai');
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan.',
        ]);
    }
}
