<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    // ─── Tampilkan halaman ganti password ─────────────────
    public function showGantiPassword()
    {
        $user = User::findOrFail(session('user_id'));
        return view('profile.ganti-password', compact('user'));
    }

    // ─── Proses ganti password ────────────────────────────
    public function gantiPassword(Request $request)
    {
        $request->validate([
            'password_lama'     => 'required|string',
            'password_baru'     => 'required|string|min:6',
            'konfirmasi_password' => 'required|same:password_baru',
        ], [
            'password_baru.min'          => 'Password baru minimal 6 karakter.',
            'konfirmasi_password.same'   => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::findOrFail(session('user_id'));

        // Cek password lama
        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors([
                'password_lama' => 'Password lama salah.',
            ]);
        }

        // Simpan password baru
        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return back()->with('success', 'Password berhasil diubah! Silakan login ulang.');
    }
}
