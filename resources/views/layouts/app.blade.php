<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HoloLens Booking')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body { background-color: #f0f2f5; }

        /* Navbar */
        .navbar-brand { font-weight: 700; letter-spacing: -0.5px; }
        .navbar { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }

        /* Cards */
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .card-header { border-radius: 12px 12px 0 0 !important; font-weight: 600; }

        /* Stat cards */
        .stat-card { border-left: 4px solid; }
        .stat-number { font-size: 2rem; font-weight: 700; }

        /* Slot table */
        .slot-table th { font-size: 13px; font-weight: 600; white-space: nowrap; }
        .slot-btn {
            width: 100%; border-radius: 6px; padding: 8px 4px;
            font-size: 12px; font-weight: 500; border: none; cursor: pointer;
            transition: all 0.15s;
        }
        .slot-empty  { background: #d1fae5; color: #065f46; }
        .slot-empty:hover { background: #6ee7b7; }
        .slot-mine   { background: #dbeafe; color: #1e3a8a; }
        .slot-taken  { background: #fee2e2; color: #7f1d1d; cursor: default; }
        .slot-time   { font-family: monospace; font-weight: 600; font-size: 13px; color: #64748b; }
    </style>

    @stack('styles')
</head>
<body>

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="{{ session('user_role') === 'admin' ? route('admin.dashboard') : route('dashboard') }}">
            <i class="bi bi-headset-vr me-2"></i>HoloLens Booking
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            @if(session('user_role') === 'admin')
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.bookings') }}"><i class="bi bi-calendar3 me-1"></i>Semua Booking</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.users') }}"><i class="bi bi-people me-1"></i>Kelompok</a></li>
                </ul>
            @else
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-house me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('schedule.index') }}"><i class="bi bi-calendar-week me-1"></i>Jadwal</a></li>
                </ul>
            @endif

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>{{ session('user_nama') }}
                        @if(session('user_role') === 'admin')
                            <span class="badge bg-warning text-dark ms-1">Admin</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                                <a href="{{ route('profile.ganti-password') }}" class="dropdown-item">
                                    <i class="bi bi-lock me-2"></i>Ganti Password
                                </a>
                                        </li>
                            <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- Flash messages --}}
<div class="container mt-3">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>

{{-- Main Content --}}
<main class="container my-4">
    @yield('content')
</main>

<footer class="text-center text-muted py-4" style="font-size:13px;">
    <i class="bi bi-headset-vr me-1"></i>HoloLens Booking System &copy; {{ date('Y') }}
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
@include('components.ai-bubble')
</body>
</html>
