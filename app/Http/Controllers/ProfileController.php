<?php

namespace App\Http\Controllers;

use App\Models\Penyuluh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        $penyuluh = null;

        if ($user->role === 'penyuluh') {
            $penyuluh = Penyuluh::with([
                    'poktan' => fn ($q) => $q->where('status', 'aktif')->with('kecamatan'),
                    'penugasanAktif.kecamatan',
                ])
                ->where('user_id', $user->id)
                ->first();
        }

        return view('profile.show', compact('user', 'penyuluh'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan akun lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        $user->update($validated);

        if ($user->role === 'penyuluh') {
            Penyuluh::where('user_id', $user->id)
                ->update([
                    'nama_penyuluh' => $validated['name'],
                ]);
        }

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors([
                    'current_password' => 'Password saat ini tidak sesuai.',
                ])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success_password', 'Password berhasil diubah.');
    }
}
