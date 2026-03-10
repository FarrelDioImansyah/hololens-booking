@extends('layouts.app')

@section('title', 'Dashboard — ' . $user->nama_kelompok)

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col">
        <h4 class="fw-bold mb-0"><i class="bi bi-house-heart me-2 text-primary"></i>Dashboard</h4>
        <p class="text-muted mb-0">Selamat datang, <strong>{{ $user->nama_kelompok }}</strong></p>
    </div>
    <div class="col-auto">
        <a href="{{ route('schedule.index') }}" class="btn btn-primary">
            <i class="bi bi-calendar-plus me-2"></i>Booking Sekarang
        </a>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color:#3b82f6">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:13px;">Sisa Jam Minggu Ini</div>
                <div class="stat-number text-primary">{{ $limit->sisaJam() }}</div>
                <div class="text-muted" style="font-size:12px;">dari {{ $limit->max_jam }} jam</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color:#10b981">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:13px;">Jam Terpakai</div>
                <div class="stat-number text-success">{{ $limit->jam_terpakai }}</div>
                <div class="text-muted" style="font-size:12px;">jam minggu ini</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color:#f59e0b">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:13px;">Total Booking</div>
                <div class="stat-number text-warning">{{ $bookingMingguIni->count() }}</div>
                <div class="text-muted" style="font-size:12px;">sesi minggu ini</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color:#ef4444">
            <div class="card-body">
                <div class="text-muted mb-1" style="font-size:13px;">Sisa Jam Hari Ini</div>
                <div class="stat-number text-danger">{{ 5 - $bookingHariIni }}</div>
                <div class="text-muted" style="font-size:12px;">dari 5 jam (maks/hari)</div>
            </div>
        </div>
    </div>
</div>

{{-- Progress bar kuota --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-semibold">Kuota Penggunaan Minggu Ini</span>
            <span class="text-muted" style="font-size:13px;">{{ $limit->jam_terpakai }} / {{ $limit->max_jam }} jam</span>
        </div>
        <div class="progress" style="height:12px; border-radius:6px;">
            @php
                $persen = $limit->persenTerpakai();
                $warna = $persen >= 90 ? 'danger' : ($persen >= 60 ? 'warning' : 'success');
            @endphp
            <div class="progress-bar bg-{{ $warna }}" style="width:{{ $persen }}%"></div>
        </div>
        @if($limit->sisaJam() === 0)
            <small class="text-danger mt-1 d-block"><i class="bi bi-exclamation-circle me-1"></i>Kuota habis. Reset setiap Senin pagi.</small>
        @endif
    </div>
</div>

{{-- Tabel booking mendatang --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar-check me-2 text-primary"></i>Booking Mendatang</span>
        <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="card-body p-0">
        @if($bookingMendatang->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x" style="font-size:40px;opacity:0.3;"></i>
                <p class="mt-3">Belum ada booking terjadwal.</p>
                <a href="{{ route('schedule.index') }}" class="btn btn-primary btn-sm">Booking Sekarang</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Perangkat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookingMendatang as $booking)
                        <tr>
                            <td>{{ $booking->tanggal->translatedFormat('D, d M Y') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ substr($booking->jam_mulai,0,5) }}–{{ substr($booking->jam_selesai,0,5) }}</span></td>
                            <td>{{ $booking->hololens->nama_alat }}</td>
                            <td><span class="badge bg-success">Aktif</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger btn-cancel" data-id="{{ $booking->id }}">
                                    <i class="bi bi-x-circle"></i> Batalkan
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-cancel').forEach(btn => {
    btn.addEventListener('click', function () {
        if (!confirm('Yakin ingin membatalkan booking ini? Jam akan dikembalikan ke kuota.')) return;

        const id = this.dataset.id;
        fetch(`/booking/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        });
    });
});
</script>
@endpush
