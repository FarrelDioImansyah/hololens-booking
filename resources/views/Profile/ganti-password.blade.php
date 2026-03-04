@extends('layouts.app')

@section('title', 'Ganti Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-lock me-2 text-primary"></i>Ganti Password
            </div>
            <div class="card-body">

                {{-- Pesan sukses --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('profile.ganti-password.post') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Lama</label>
                        <input type="password" name="password_lama"
                            class="form-control @error('password_lama') is-invalid @enderror"
                            placeholder="Masukkan password lama">
                        @error('password_lama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru</label>
                        <input type="password" name="password_baru"
                            class="form-control @error('password_baru') is-invalid @enderror"
                            placeholder="Minimal 6 karakter">
                        @error('password_baru')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi_password"
                            class="form-control @error('konfirmasi_password') is-invalid @enderror"
                            placeholder="Ulangi password baru">
                        @error('konfirmasi_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>Simpan Password Baru
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
