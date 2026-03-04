@extends('layouts.app')

@section('title', 'Jadwal Booking')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="fw-bold mb-0"><i class="bi bi-calendar-week me-2 text-primary"></i>Jadwal Booking</h4>
        <p class="text-muted mb-0">Klik slot <span class="badge" style="background:#d1fae5;color:#065f46;">KOSONG</span> untuk booking</p>
    </div>
    <div class="col-auto">
        <span class="fw-semibold text-primary">Sisa: {{ $limit->sisaJam() }}/{{ $limit->max_jam }} jam</span>
    </div>
</div>

{{-- Navigasi Tanggal --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('schedule.index') }}" class="d-flex align-items-center gap-3 flex-wrap">
            <label class="fw-semibold mb-0">Pilih Tanggal:</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}"
                   min="{{ \Carbon\Carbon::today()->toDateString() }}"
                   class="form-control w-auto">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-search me-1"></i>Tampilkan
            </button>
            <a href="{{ route('schedule.index') }}" class="btn btn-outline-secondary btn-sm">Hari Ini</a>
            <span class="ms-auto text-muted" style="font-size:14px;">
                <i class="bi bi-calendar3 me-1"></i>
                {{ $tanggalCarbon->translatedFormat('l, d F Y') }}
            </span>
        </form>
    </div>
</div>

{{-- Legenda --}}
<div class="d-flex gap-3 mb-3 flex-wrap" style="font-size:13px;">
    <span><span class="badge px-3 py-2" style="background:#d1fae5;color:#065f46;">KOSONG</span> Bisa dibooking</span>
    <span><span class="badge px-3 py-2" style="background:#dbeafe;color:#1e3a8a;">SAYA</span> Booking saya</span>
    <span><span class="badge px-3 py-2" style="background:#fee2e2;color:#7f1d1d;">TERISI</span> Sudah ada yang booking</span>
</div>

{{-- Tabel Jadwal --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table slot-table mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:80px;">Jam</th>
                        @foreach($hololensList as $hl)
                            <th class="text-center">
                                <i class="bi bi-headset-vr me-1"></i>{{ $hl->nama_alat }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($slots as $jam)
                    <tr>
                        <td class="slot-time align-middle">{{ $jam }}</td>

                        @foreach($hololensList as $hl)
                            @php
                                // Cari booking untuk slot ini
                                $key     = $hl->id . '_' . $jam . ':00';
                                $booking = $bookings->get($key)?->first();
                                $isMineBooking = $booking && $booking->user_id == session('user_id');
                                $isTaken = $booking && !$isMineBooking;
                            @endphp
                            <td class="text-center">
                                @if($isTaken)
                                    {{-- Slot sudah diisi orang lain --}}
                                    <button class="slot-btn slot-taken" disabled>
                                        <i class="bi bi-lock-fill me-1"></i>{{ $booking->user->nama_kelompok }}
                                    </button>
                                @elseif($isMineBooking)
                                    {{-- Slot milik saya --}}
                                    <button class="slot-btn slot-mine btn-cancel-slot"
                                        data-id="{{ $booking->id }}"
                                        title="Klik untuk batalkan">
                                        <i class="bi bi-check-circle me-1"></i>SAYA
                                    </button>
                                @else
                                    {{-- Slot kosong --}}
                                    @if($limit->sisaJam() > 0)
                                        <button class="slot-btn slot-empty btn-book"
                                            data-hololens="{{ $hl->id }}"
                                            data-tanggal="{{ $tanggal }}"
                                            data-jam="{{ $jam }}"
                                            data-nama="{{ $hl->nama_alat }}">
                                            <i class="bi bi-plus-circle me-1"></i>BOOK
                                        </button>
                                    @else
                                        <button class="slot-btn" style="background:#f1f5f9;color:#94a3b8;" disabled>
                                            Kuota habis
                                        </button>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Booking --}}
<div class="modal fade" id="modalBooking" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Konfirmasi Booking</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Kamu akan melakukan booking:</p>
                <table class="table table-sm">
                    <tr><td class="text-muted">Perangkat</td><td id="conf-hololens" class="fw-semibold"></td></tr>
                    <tr><td class="text-muted">Tanggal</td><td id="conf-tanggal" class="fw-semibold"></td></tr>
                    <tr><td class="text-muted">Jam</td><td id="conf-jam" class="fw-semibold"></td></tr>
                    <tr><td class="text-muted">Sisa Kuota</td><td><span id="conf-sisa" class="badge bg-primary"></span></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnConfirmBook" class="btn btn-primary">
                    <span id="btnText"><i class="bi bi-check-circle me-2"></i>Ya, Booking!</span>
                    <span id="btnLoading" class="d-none"><span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast notifikasi --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="toast" class="toast align-items-center text-white border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const modal   = new bootstrap.Modal(document.getElementById('modalBooking'));
const toast   = new bootstrap.Toast(document.getElementById('toast'), { delay: 3500 });
let pendingData = {};

// Klik slot kosong → buka modal konfirmasi
document.querySelectorAll('.btn-book').forEach(btn => {
    btn.addEventListener('click', function () {
        const jam = this.dataset.jam;
        const jamSelesai = String(parseInt(jam) + 1).padStart(2, '0') + ':00';

        pendingData = {
            hololens_id: this.dataset.hololens,
            tanggal: this.dataset.tanggal,
            jam_mulai: jam,
        };

        document.getElementById('conf-hololens').textContent = this.dataset.nama;
        document.getElementById('conf-tanggal').textContent  = formatTanggal(this.dataset.tanggal);
        document.getElementById('conf-jam').textContent      = jam + ' – ' + jamSelesai;
        document.getElementById('conf-sisa').textContent     = '{{ $limit->sisaJam() }} jam tersisa';
        modal.show();
    });
});

// Konfirmasi booking
document.getElementById('btnConfirmBook').addEventListener('click', function () {
    document.getElementById('btnText').classList.add('d-none');
    document.getElementById('btnLoading').classList.remove('d-none');
    this.disabled = true;

    fetch('/booking', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(pendingData)
    })
    .then(r => r.json())
    .then(data => {
        modal.hide();
        showToast(data.message, data.success ? 'success' : 'danger');
        if (data.success) setTimeout(() => location.reload(), 1200);
    })
    .finally(() => {
        document.getElementById('btnText').classList.remove('d-none');
        document.getElementById('btnLoading').classList.add('d-none');
        document.getElementById('btnConfirmBook').disabled = false;
    });
});

// Batalkan booking sendiri
document.querySelectorAll('.btn-cancel-slot').forEach(btn => {
    btn.addEventListener('click', function () {
        if (!confirm('Batalkan booking ini? Jam akan dikembalikan ke kuota.')) return;
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
            showToast(data.message, data.success ? 'success' : 'danger');
            if (data.success) setTimeout(() => location.reload(), 1200);
        });
    });
});

function showToast(msg, type) {
    const el = document.getElementById('toast');
    el.className = `toast align-items-center text-white border-0 bg-${type}`;
    document.getElementById('toastMsg').textContent = msg;
    toast.show();
}

function formatTanggal(str) {
    const d = new Date(str);
    return d.toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
}
</script>
@endpush
