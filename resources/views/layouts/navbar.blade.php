<style>
    .app-topbar {
        min-height: 72px;
        background: #fff;
        border-bottom: 1px solid #edf0f4;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
        padding-left: 20px;
        padding-right: 22px;
    }

    .topbar-title {
        color: #1f2937;
        font-size: .92rem;
        font-weight: 700;
        margin: 0;
    }

    .topbar-subtitle {
        color: #9ca3af;
        font-size: .70rem;
        margin-top: 2px;
    }

    .profile-toggle {
        display: flex;
        align-items: center;
        padding: 7px 9px !important;
        border-radius: 10px;
        color: #374151 !important;
        text-decoration: none !important;
    }

    .profile-toggle:hover {
        background: #f8fafc;
    }

    .profile-avatar {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #dcfce7;
        color: #15803d;
        font-weight: 800;
        margin-right: 10px;
    }

    .profile-name {
        color: #1f2937;
        font-size: .80rem;
        font-weight: 700;
        line-height: 1.15;
    }

    .profile-role {
        color: #9ca3af;
        font-size: .66rem;
        margin-top: 2px;
    }

    .profile-dropdown {
        min-width: 205px;
        border: 0;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(15,23,42,.12);
        padding: 8px;
    }

    .profile-dropdown .dropdown-item {
        border-radius: 7px;
        padding: 9px 11px;
        font-size: .80rem;
    }

    .profile-dropdown .dropdown-item i {
        width: 20px;
        color: #6b7280;
        margin-right: 6px;
    }
</style>

<nav class="navbar navbar-expand navbar-light app-topbar mb-4">

    <button id="sidebarToggleTop"
            class="btn btn-link d-md-none rounded-circle mr-2">
        <i class="fa fa-bars"></i>
    </button>

    <div class="d-none d-md-block">
        <div class="topbar-title">Sistem Penentuan Prioritas Subsidi Pupuk</div>
        <div class="topbar-subtitle">Dinas Pertanian</div>
    </div>

    <ul class="navbar-nav ml-auto">
        @auth
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle profile-toggle"
                   href="#"
                   id="profileDropdown"
                   role="button"
                   data-toggle="dropdown"
                   aria-haspopup="true"
                   aria-expanded="false">

                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="d-none d-sm-block text-left mr-2">
                        <div class="profile-name">{{ auth()->user()->name }}</div>
                        <div class="profile-role">
                            {{ auth()->user()->role === 'admin' ? 'Admin' : 'Penyuluh' }}
                        </div>
                    </div>

                    <i class="fas fa-chevron-down text-gray-400" style="font-size:.65rem;"></i>
                </a>

                <div class="dropdown-menu dropdown-menu-right profile-dropdown"
                     aria-labelledby="profileDropdown">

                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i class="fas fa-user-circle"></i>
                        Profil Saya
                    </a>

                    <a class="dropdown-item" href="{{ route('profile.show') }}#password">
                        <i class="fas fa-key"></i>
                        Ubah Password
                    </a>

                    <div class="dropdown-divider"></div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt text-danger"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </li>
        @endauth
    </ul>

</nav>
