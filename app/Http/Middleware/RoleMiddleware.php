<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    
    // Mengecek hak akses berdasarkan role user.
    // Bisa menerima lebih dari satu role.
    
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Jika user belum login, arahkan ke halaman login
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Ubah role user dan allowed roles ke huruf kecil untuk perbandingan yang tidak case-sensitive
        $userRole = strtolower(auth()->user()->role);
        $allowedRoles = array_map('strtolower', $roles);

        // Jika role user tidak ada di daftar role yang diizinkan
        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Akses ditolak');
        }

        // Jika role sesuai, lanjutkan akses halaman
        return $next($request);
    }
}