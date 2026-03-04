<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Hololens;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1 akun Admin ──────────────────────────────────
        User::create([
            'nama_kelompok' => 'Administrator',
            'username'      => 'admin',
            'password'      => Hash::make('admin123'),
            'role'          => 'admin',
        ]);

        // ── 8 akun Kelompok ───────────────────────────────
        for ($i = 1; $i <= 8; $i++) {
            User::create([
                'nama_kelompok' => "Kelompok $i",
                'username'      => "kelompok$i",
                'password'      => Hash::make("kelompok$i"),  // password sama dengan username
                'role'          => 'kelompok',
            ]);
        }

        // ── 2 perangkat HoloLens ──────────────────────────
        Hololens::create(['nama_alat' => 'HoloLens 1', 'status' => 'aktif']);
        Hololens::create(['nama_alat' => 'HoloLens 2', 'status' => 'aktif']);

        $this->command->info('Seeder selesai: 1 admin + 8 kelompok + 2 HoloLens');
    }
}
