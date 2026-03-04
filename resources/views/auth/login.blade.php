<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — HoloLens Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            width: 100%; max-width: 420px;
            background: white; border-radius: 16px;
            padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-icon {
            width: 64px; height: 64px; border-radius: 16px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; color: white; margin: 0 auto 20px;
        }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        .btn-login { background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none; padding: 12px; font-weight: 600; }
        .info-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 12px 16px; font-size: 13px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-icon"><i class="bi bi-headset-vr"></i></div>
    <h4 class="text-center fw-bold mb-1">HoloLens Booking</h4>
    <p class="text-center text-muted mb-4" style="font-size:14px;">Sistem Peminjaman Laboratorium</p>

    {{-- Error message --}}
    @if($errors->has('login'))
        <div class="alert alert-danger py-2 mb-3" style="font-size:14px;">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first('login') }}
        </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                    value="{{ old('username') }}" placeholder="contoh: kelompok1" autofocus>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                    placeholder="Masukkan password">
            </div>
        </div>
        <button type="submit" class="btn btn-login btn-primary w-100 rounded-3">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </button>
    </form>

    <div class="info-box mt-4">
        <strong><i class="bi bi-info-circle me-1"></i>Akun Default:</strong><br>
        Kelompok: <code>kelompok1</code> s/d <code>kelompok8</code><br>
        Password = username (misal: <code>kelompok1</code>)<br>
        Admin: <code>admin</code> / <code>admin123</code>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
