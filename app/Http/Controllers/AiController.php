<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Booking;
use App\Models\Hololens;
use App\Models\User;
use Carbon\Carbon;

class AiController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $userId  = session('user_id');
        $user    = User::findOrFail($userId);
        $message = $request->message;

        $today    = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        // Booking mendatang milik user
        $bookingMendatang = Booking::aktif()
            ->where('user_id', $userId)
            ->where('tanggal', '>=', $today)
            ->with('hololens')
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn($b) => [
                'id'         => $b->id,
                'tanggal'    => $b->tanggal->format('Y-m-d'),
                'jam_mulai'  => substr($b->jam_mulai, 0, 5),
                'jam_selesai'=> substr($b->jam_selesai, 0, 5),
                'hololens'   => $b->hololens->nama_alat,
            ]);

        // Limit mingguan
        $limit = $user->getOrCreateWeeklyLimit();

        // Booking hari ini
        $bookingHariIni = Booking::aktif()
            ->where('user_id', $userId)
            ->where('tanggal', $today)
            ->count();

        // Slot tersedia hari ini
        $hololensList = Hololens::aktif()->get();
        $bookingToday = Booking::aktif()
            ->where('tanggal', $today)
            ->get()
            ->groupBy(fn($b) => $b->hololens_id . '_' . substr($b->jam_mulai, 0, 5));

        $slotTersedia = [];
        foreach ($hololensList as $hl) {
            for ($jam = 8; $jam < 21; $jam++) {
                $key = $hl->id . '_' . sprintf('%02d:00', $jam);
                if (!isset($bookingToday[$key])) {
                    $slotTersedia[] = sprintf('%s jam %02d:00-%02d:00', $hl->nama_alat, $jam, $jam + 1);
                }
            }
        }
        $slotTersediaStr = implode(', ', array_slice($slotTersedia, 0, 10));
        $sisaHariIni = ($limit->max_jam_harian ?? 5) - $bookingHariIni;
        // ── System prompt ─────────────────────────────────
        $systemPrompt = <<<EOT
Kamu adalah asisten AI untuk sistem HoloLens Booking di laboratorium. Nama kamu adalah "HoloBot".

DATA USER SAAT INI:
- Nama: {$user->nama_kelompok}
- Sisa kuota minggu ini: {$limit->sisaJam()} jam (dari {$limit->max_jam} jam)
- Booking hari ini: {$bookingHariIni} jam (maks {$limit->max_jam_harian} jam/hari)
- Sisa jam hari ini: {$sisaHariIni} jam
- Tanggal hari ini: {$today}
- Besok: {$tomorrow}

BOOKING MENDATANG USER:
{$bookingMendatang->toJson(JSON_PRETTY_PRINT)}

SLOT TERSEDIA HARI INI (sebagian):
{$slotTersediaStr}

KEMAMPUAN KAMU:
1. Menjawab pertanyaan tentang jadwal booking user
2. Memberikan saran slot yang tersedia
3. Untuk BOOKING baru: balas HANYA dengan JSON murni tanpa teks lain:
   {"action":"book","hololens_id":1,"tanggal":"2026-03-10","jam_mulai":"12:00","jam_selesai":"16:00"}
4. Untuk BATALKAN booking: balas HANYA dengan JSON murni tanpa teks lain:
   {"action":"cancel","booking_id":123}
5. Jika bukan aksi booking/cancel, balas dengan teks biasa

CARA BOOKING:
- Jika user minta booking tapi belum sebut tanggal → tanya tanggalnya
- Jika user minta booking tapi belum sebut jam → tanya jamnya (08:00-20:00)
- Jika user bilang "besok" → gunakan tanggal {$tomorrow}
- Jika user bilang "hari ini" → gunakan tanggal {$today}
- Jika semua info lengkap (tanggal + jam mulai + jam selesai) → langsung balas JSON booking
- hololens_id selalu gunakan 1 jika tidak disebutkan
- PENTING: Jika membalas JSON, jangan tambahkan teks apapun, hanya JSON saja

ATURAN:
- Selalu ramah dan sopan
- Gunakan Bahasa Indonesia
- Maksimal booking 5 jam/hari dan sesuai kuota mingguan
- Jam operasional: 08:00 - 20:00
- Jangan booking 2 HoloLens di jam yang sama

Balas singkat dan jelas!
EOT;

        // ── Panggil Gemini API ────────────────────────────
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . config('services.gemini.key'), [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $systemPrompt . "\n\nUser: " . $message]
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 500,
            ]
        ]);

        if (!$response->successful()) {
            return response()->json(['reply' => 'Maaf, AI sedang tidak tersedia. Coba lagi nanti.']);
        }

        $aiReply = $response->json('candidates.0.content.parts.0.text') ?? '';

        // ── Cek apakah AI balas dengan JSON action ────────
        $decoded = null;
        if (str_contains($aiReply, '"action"')) {
            preg_match('/\{.*\}/s', $aiReply, $matches);
            if (!empty($matches)) {
                $decoded = json_decode($matches[0], true);
            }
        }

        if ($decoded && isset($decoded['action'])) {
            if ($decoded['action'] === 'book') {
                return $this->doBooking($decoded, $user, $limit, $bookingHariIni);
            }
            if ($decoded['action'] === 'cancel') {
                return $this->doCancel($decoded, $user);
            }
        }

        // Bersihkan jika ada sisa JSON di reply teks biasa
        $aiReplyClean = preg_replace('/```json.*?```/s', '', $aiReply);
        $aiReplyClean = preg_replace('/\{[^{}]*"action"[^{}]*\}/s', '', $aiReplyClean);
        $aiReplyClean = trim($aiReplyClean);

        return response()->json(['reply' => $aiReplyClean ?: 'Maaf, saya tidak mengerti. Coba lagi!']);
    }

    private function doBooking($data, $user, $limit, $bookingHariIni)
    {
        // Validasi kuota
        if ($limit->sisaJam() <= 0) {
            return response()->json(['reply' => 'Maaf, kuota mingguan kamu sudah habis! Tunggu reset hari Senin.']);
        }

        if ($bookingHariIni >= $limit->max_jam_harian) {
            return response()->json(['reply' => 'Maaf, kamu sudah mencapai batas 5 jam hari ini!']);
        }

        $jamMulaiInt   = (int) substr($data['jam_mulai'], 0, 2);
        $jamSelesaiInt = isset($data['jam_selesai'])
            ? (int) substr($data['jam_selesai'], 0, 2)
            : $jamMulaiInt + 1;

        $totalJam = $jamSelesaiInt - $jamMulaiInt;

        if ($totalJam <= 0 || $totalJam > 5) {
            return response()->json(['reply' => 'Durasi booking tidak valid. Maksimal 5 jam sekaligus.']);
        }

        // Cek semua slot yang akan dipesan
        for ($jam = $jamMulaiInt; $jam < $jamSelesaiInt; $jam++) {
            $jamMulaiCek = sprintf('%02d:00:00', $jam);

            $slotTerisi = Booking::aktif()
                ->where('hololens_id', $data['hololens_id'])
                ->where('tanggal', $data['tanggal'])
                ->where('jam_mulai', $jamMulaiCek)
                ->exists();

            if ($slotTerisi) {
                return response()->json(['reply' => "Maaf, slot jam " . sprintf('%02d:00', $jam) . " sudah diambil. Mau pilih jam lain?"]);
            }

            $double = Booking::aktif()
                ->where('user_id', $user->id)
                ->where('tanggal', $data['tanggal'])
                ->where('jam_mulai', $jamMulaiCek)
                ->exists();

            if ($double) {
                return response()->json(['reply' => "Kamu sudah punya booking jam " . sprintf('%02d:00', $jam) . ". Tidak bisa booking 2 HoloLens di jam yang sama!"]);
            }
        }

        // Buat booking per jam
        for ($jam = $jamMulaiInt; $jam < $jamSelesaiInt; $jam++) {
            Booking::create([
                'user_id'     => $user->id,
                'hololens_id' => $data['hololens_id'],
                'tanggal'     => $data['tanggal'],
                'jam_mulai'   => sprintf('%02d:00:00', $jam),
                'jam_selesai' => sprintf('%02d:00:00', $jam + 1),
                'status'      => 'aktif',
            ]);
            $limit->increment('jam_terpakai');
        }

        $hololens = Hololens::find($data['hololens_id']);
        $sisaKuota = $limit->max_jam - $limit->fresh()->jam_terpakai;

        return response()->json([
            'reply'  => "✅ Booking berhasil! {$hololens->nama_alat} pada {$data['tanggal']} jam " . sprintf('%02d:00', $jamMulaiInt) . "–" . sprintf('%02d:00', $jamSelesaiInt) . " ({$totalJam} jam). Sisa kuota: {$sisaKuota} jam.",
            'reload' => true
        ]);
    }

    private function doCancel($data, $user)
    {
        $booking = Booking::find($data['booking_id']);

        if (!$booking || $booking->user_id !== $user->id) {
            return response()->json(['reply' => 'Booking tidak ditemukan atau bukan milikmu.']);
        }

        $booking->update(['status' => 'dibatalkan']);
        $limit = $user->getOrCreateWeeklyLimit();
        if ($limit->jam_terpakai > 0) {
            $limit->decrement('jam_terpakai');
        }

        return response()->json([
            'reply'  => "✅ Booking berhasil dibatalkan! 1 jam dikembalikan ke kuota kamu.",
            'reload' => true
        ]);
    }
}