@extends('layouts.app')

@section('content')

<style>
    .profile-card {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .profile-summary {
        padding: 24px;
        background: linear-gradient(135deg, #0f766e, #16a34a);
        color: #fff;
    }

    .profile-avatar-lg {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255,255,255,.18);
        border: 1px solid rgba(255,255,255,.25);
        font-size: 1.35rem;
        font-weight: 700;
    }

    .profile-section-title {
        color: #1f2937;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .profile-section-desc {
        color: #6b7280;
        font-size: .84rem;
    }

    .form-control {
        border-radius: 8px;
        min-height: 44px;
    }

    .profile-divider {
        border-top: 1px solid #edf0f4;
        margin: 28px 0;
    }
</style>

<div class="container-fluid pb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold text-gray-800 mb-1">
                Profil Saya
            </h4>
            <p class="text-muted mb-0">
                Kelola informasi akun yang sedang Anda gunakan.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-light border">
            <i class="fas fa-arrow-left mr-1"></i>
            Kembali
        </a>
    </div>

    @if(session('success_password'))
        <div class="alert alert-success shadow-sm">
            <i class="fas fa-key mr-2"></i>
            {{ session('success_password') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <div class="font-weight-bold mb-2">Terdapat data yang perlu diperbaiki:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-4 mb-4">
            <div class="profile-card">
                <div class="profile-summary">
                    <div class="profile-avatar-lg mb-3">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h5 class="font-weight-bold mb-1">{{ $user->name }}</h5>
                    <div class="small" style="opacity:.9;">
                        {{ $user->role === 'admin' ? 'Administrator' : 'Penyuluh' }}
                    </div>
                </div>

                <div class="p-4 bg-white">
                    <div class="small text-muted mb-1">Username</div>
                    <div class="font-weight-bold text-gray-800 mb-3">{{ $user->username }}</div>

                    <div class="small text-muted mb-1">Email</div>
                    <div class="font-weight-bold text-gray-800">{{ $user->email }}</div>

                    @if($penyuluh)
                        <div class="profile-divider"></div>
                        <div class="small text-muted mb-1">Wilayah Tugas Aktif</div>
                        <div class="font-weight-bold text-gray-800 mb-3">{{ $penyuluh->penugasanAktif?->kecamatan?->nama_kecamatan ?? 'Belum ditetapkan' }}</div>
                        <div class="small text-muted mb-2">Poktan Binaan Aktif</div>
                        @forelse($penyuluh->poktan as $poktan)
                            <div class="mb-2">
                                <span class="badge badge-light px-3 py-2">
                                    {{ $poktan->nama_poktan }}
                                    @if($poktan->kecamatan)
                                        · {{ $poktan->kecamatan->nama_kecamatan }}
                                    @endif
                                </span>
                            </div>
                        @empty
                            <div class="text-muted small">Belum memiliki Poktan binaan.</div>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card profile-card mb-4">
                <div class="card-body p-4">
                    <div class="profile-section-title">Informasi Profil</div>
                    <div class="profile-section-desc mb-4">
                        Perubahan di sini hanya berlaku untuk akun Anda sendiri.
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="font-weight-bold">Nama</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Username</label>
                                <input type="text"
                                       name="username"
                                       class="form-control"
                                       value="{{ old('username', $user->username) }}"
                                       required>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Email</label>
                                <input type="email"
                                       name="email"
                                       class="form-control"
                                       value="{{ old('email', $user->email) }}"
                                       required>
                            </div>
                        </div>

                        <div class="text-right">
                            <button class="btn btn-success px-4">
                                <i class="fas fa-save mr-1"></i>
                                Simpan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card profile-card" id="password">
                <div class="card-body p-4">
                    <div class="profile-section-title">Ubah Password</div>
                    <div class="profile-section-desc mb-4">
                        Gunakan password yang hanya Anda ketahui dan mudah Anda ingat.
                    </div>

                    <form method="POST" action="{{ route('profile.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="font-weight-bold">Password Saat Ini</label>
                            <input type="password"
                                   name="current_password"
                                   class="form-control"
                                   autocomplete="current-password"
                                   required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Password Baru</label>
                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       autocomplete="new-password"
                                       required>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Konfirmasi Password Baru</label>
                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control"
                                       autocomplete="new-password"
                                       required>
                            </div>
                        </div>

                        <div class="text-right">
                            <button class="btn btn-dark px-4">
                                <i class="fas fa-key mr-1"></i>
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
