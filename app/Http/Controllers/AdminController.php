<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', 'admin');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $admins = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama Admin wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan akun lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Data Admin berhasil ditambahkan.');
    }

    public function edit(User $admin)
    {
        abort_unless($admin->role === 'admin', 404);

        return view('admin.edit', compact('admin'));
    }

    public function update(Request $request, User $admin)
    {
        abort_unless($admin->role === 'admin', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($admin->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($admin->id),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $admin->update($data);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Data Admin berhasil diperbarui.');
    }

    public function destroy(User $admin)
    {
        abort_unless($admin->role === 'admin', 404);

        if ((int) $admin->id === (int) Auth::id()) {
            return back()->with(
                'error',
                'Akun Admin yang sedang digunakan tidak dapat dihapus dari menu Data Admin.'
            );
        }

        if (User::where('role', 'admin')->count() <= 1) {
            return back()->with(
                'error',
                'Minimal harus tersedia satu akun Admin di dalam sistem.'
            );
        }

        $admin->delete();

        return redirect()
            ->route('admin.index')
            ->with('success', 'Data Admin berhasil dihapus.');
    }
}
