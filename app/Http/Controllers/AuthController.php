<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function index()
    {
        return view('auth.login');
    }

    // Proses login menggunakan username dan password
    public function login(Request $request)
    {
        // Validasi input login
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Mengecek username dan password ke tabel users
        if (Auth::attempt($credentials)) {
            // Membuat ulang session agar aman
            $request->session()->regenerate();

            // SEMUA USER DIARAHKAN KE /dashboard
            // Nanti route /dashboard yang akan redirect sesuai role
            return redirect()->intended('/dashboard');
        }

        // Jika login gagal
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }

    // Logout user
    public function logout(Request $request)
    {
        // Menghapus sesi login
        Auth::logout();

        // Menghapus semua session
        $request->session()->invalidate();

        // Membuat token session baru
        $request->session()->regenerateToken();

        // Arahkan kembali ke halaman login
        return redirect('/login');
    }
}