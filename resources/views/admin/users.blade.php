@extends('layouts.app')

@section('title', 'Kelola Kelompok — Admin')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col">
        <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Kelola Akun Kelompok</h4>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle me-2"></i>Tambah Kelompok
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Kelompok</th>
                        <th>Username</th>
                        <th>Jam Terpakai</th>
                        <th>Batas Jam</th>
                        <th>Kuota Tersisa</th>
                        <th>Atur Limit Mingguan</th>
                        <th>Atur Limit Harian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    @php
                        $limit = $u->usageLimits->first();
                        $terpakai = $limit?->jam_terpakai ?? 0;
                        $maxJam   = $limit?->max_jam ?? 20;
                        $maxHarian = $limit?->max_jam_harian ?? 5;
                        $sisa     = max(0, $maxJam - $terpakai);
                        $persen   = $maxJam > 0 ? min(100, round($terpakai/$maxJam*100)) : 0;
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $u->nama_kelompok }}</td>
                        <td><code>{{ $u->username }}</code></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;min-width:80px;">
                                    <div class="progress-bar {{ $persen >= 90 ? 'bg-danger' : ($persen >= 60 ? 'bg-warning' : 'bg-success') }}"
                                         style="width:{{ $persen }}%"></div>
                                </div>
                                <span style="font-size:13px;">{{ $terpakai }}</span>
                            </div>
                        </td>
                        <td>{{ $maxJam }} jam</td>
                        <td><span class="badge {{ $sisa > 0 ? 'bg-success' : 'bg-danger' }}">{{ $sisa }} jam</span></td>
                        <td>
                            <form action="{{ route('admin.users.limit', $u->id) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="number" name="max_jam" value="{{ $maxJam }}" min="1" max="100"
                                       class="form-control form-control-sm" style="width:80px;" title="Limit mingguan">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Simpan</button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('admin.users.limit.harian', $u->id) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="number" name="max_jam_harian" value="{{ $maxHarian }}" min="1" max="13"
                                       class="form-control form-control-sm" style="width:80px;" title="Limit harian (maks 13 jam)">
                                <button type="submit" class="btn btn-sm btn-outline-warning">Simpan</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Kelompok --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Tambah Kelompok</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kelompok</label>
                        <input type="text" name="nama_kelompok" class="form-control" placeholder="contoh: Kelompok 9" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="contoh: kelompok9" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection