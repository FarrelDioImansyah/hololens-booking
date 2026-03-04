<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Cegah akses halaman admin oleh non-admin.
     * Harus digunakan setelah AuthMiddleware.
     */
    public function handle(Request $request, Closure $next)
    {
        if (session('user_role') !== 'admin') {
            abort(403, 'Halaman ini hanya untuk administrator.');
        }

        return $next($request);
    }
}
