@extends('layouts.app')

@section('title', 'Semua Booking — Admin')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-calendar3 me-2 text-primary"></i>Semua Booking</h4>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
            <div>
                <label class="form-label mb-1 small">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control form-control-sm">
            </div>
            <div>
                <label class="form-label mb-1 small">Kelompok</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($kelompoks as $k)
                        <option value="{{ $k->id }}" {{ request('user_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelompok }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label mb-1 small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('admin.bookings') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Kelompok</th>
                        <th>Perangkat</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                    <tr>
                        <td class="text-muted" style="font-size:12px;">{{ $b->id }}</td>
                        <td class="fw-semibold">{{ $b->user->nama_kelompok }}</td>
                        <td>{{ $b->hololens->nama_alat }}</td>
                        <td>{{ $b->tanggal->format('d/m/Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ substr($b->jam_mulai,0,5) }}–{{ substr($b->jam_selesai,0,5) }}</span></td>
                        <td>
                            <span class="badge {{ $b->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($b->status) }}
                            </span>
                        </td>
                        <td>
                            @if($b->status === 'aktif')
                            <form action="{{ route('admin.booking.destroy', $b->id) }}" method="POST"
                                  onsubmit="return confirm('Hapus booking ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada data booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="px-3 py-2 border-top">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
