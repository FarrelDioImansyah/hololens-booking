@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Dashboard</h4>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color:#3b82f6">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:13px;">Total Booking Aktif</div>
                <div class="stat-number text-primary">{{ $totalBooking }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color:#10b981">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:13px;">Booking Hari Ini</div>
                <div class="stat-number text-success">{{ $bookingHariIni }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color:#f59e0b">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:13px;">Booking Minggu Ini</div>
                <div class="stat-number text-warning">{{ $bookingMingguIni }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color:#8b5cf6">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:13px;">Total Kelompok</div>
                <div class="stat-number" style="color:#8b5cf6">{{ $totalKelompok }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Penggunaan per kelompok --}}
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-bar-chart me-2 text-primary"></i>Penggunaan per Kelompok Minggu Ini
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Kelompok</th><th class="text-center">Booking</th></tr>
                        </thead>
                        <tbody>
                            @forelse($statsKelompok as $stat)
                            <tr>
                                <td>{{ $stat['nama'] }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $stat['jumlah'] > 0 ? 'bg-primary' : 'bg-light text-muted' }}">
                                        {{ $stat['jumlah'] }} sesi
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-4">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-lightning me-2 text-warning"></i>Aksi Cepat</div>
            <div class="card-body d-flex flex-column gap-3">
                <a href="{{ route('admin.bookings') }}" class="btn btn-outline-primary">
                    <i class="bi bi-calendar3 me-2"></i>Lihat Semua Booking
                </a>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-success">
                    <i class="bi bi-people me-2"></i>Kelola Akun Kelompok
                </a>
                <a href="{{ route('schedule.index') }}" class="btn btn-outline-info">
                    <i class="bi bi-eye me-2"></i>Lihat Jadwal
                </a>
                <form action="{{ route('admin.reset.limits') }}" method="POST"
                      onsubmit="return confirm('Reset jam semua kelompok minggu ini?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Jam Minggu Ini
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
