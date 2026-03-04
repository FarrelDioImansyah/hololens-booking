<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // ─── Tampilkan halaman login ───────────────────────────
    public function showLogin()
    {
        // Jika sudah login, redirect ke dashboard
        if (session('user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // ─── Proses login ─────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Cari user berdasarkan username
        $user = User::where('username', $request->username)->first();

        // Cek user ada dan password cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'login' => 'Username atau password salah.',
            ])->withInput(['username' => $request->username]);
        }

        // Simpan data ke session
        session([
            'user_id'        => $user->id,
            'user_nama'      => $user->nama_kelompok,
            'user_username'  => $user->username,
            'user_role'      => $user->role,
        ]);

        // Redirect berdasarkan role
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }

    // ─── Logout ───────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login')->with('success', 'Kamu berhasil logout.');
    }
}
