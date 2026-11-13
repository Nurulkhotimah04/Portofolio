<style>
    .custom-sidebar {
        position: fixed !important;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 240px !important;
        min-width: 240px !important;
        max-width: 240px !important;
        height: 100vh;
        overflow-y: auto;
        overflow-x: hidden;
        background: linear-gradient(180deg, #16a34a 0%, #15803d 58%, #166534 100%) !important;
        box-shadow: 8px 0 24px rgba(15, 118, 110, .08);
    }

    .custom-sidebar .sidebar-brand {
        height: 94px;
        padding: 14px 18px;
        color: #fff !important;
        text-decoration: none !important;
        border-bottom: 1px solid rgba(255,255,255,.13);
    }

    .brand-mark {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.16);
        border: 1px solid rgba(255,255,255,.20);
        flex: 0 0 42px;
    }

    .brand-title {
        font-size: .92rem;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: .3px;
    }

    .brand-subtitle {
        margin-top: 3px;
        font-size: .67rem;
        opacity: .82;
        font-weight: 500;
        letter-spacing: .2px;
    }

    .sidebar-section-label {
        padding: 16px 22px 7px;
        color: rgba(255,255,255,.62);
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
    }

    .custom-sidebar .nav-item {
        width: 100%;
    }

    .custom-sidebar .nav-link {
        display: flex !important;
        align-items: center;
        width: calc(100% - 20px) !important;
        margin: 3px 10px !important;
        padding: 12px 13px !important;
        border-radius: 9px;
        color: rgba(255,255,255,.92) !important;
        font-size: .86rem;
        font-weight: 500;
        transition: .18s ease;
        text-decoration: none !important;
    }

    .custom-sidebar .nav-link i {
        width: 24px;
        min-width: 24px;
        margin-right: 8px;
        text-align: center;
        color: rgba(255,255,255,.86);
    }

    .custom-sidebar .nav-link:hover,
    .custom-sidebar .nav-link.active {
        background: rgba(255,255,255,.15) !important;
        color: #fff !important;
    }

    .custom-sidebar .nav-link.active {
        font-weight: 700;
        box-shadow: inset 3px 0 0 rgba(255,255,255,.85);
    }

    .custom-sidebar .nav-link[data-toggle="collapse"]::after {
        margin-left: auto;
    }

    .custom-sidebar .submenu-box {
        margin: 0 12px 8px;
        padding: 6px;
        border-radius: 10px;
        background: rgba(0,0,0,.08);
    }

    .custom-sidebar .collapse-item {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 9px 10px;
        margin-bottom: 2px;
        border-radius: 7px;
        color: rgba(255,255,255,.88) !important;
        font-size: .80rem;
        text-decoration: none !important;
        transition: .18s ease;
        box-sizing: border-box;
    }

    .custom-sidebar .collapse-item i {
        width: 20px;
        min-width: 20px;
        text-align: center;
        margin-right: 7px;
    }

    .custom-sidebar .collapse-item:hover,
    .custom-sidebar .collapse-item.active {
        color: #fff !important;
        background: rgba(255,255,255,.13);
    }

    .custom-sidebar .collapse-item.active {
        font-weight: 700;
    }

    .custom-sidebar::-webkit-scrollbar {
        width: 5px;
    }

    .custom-sidebar::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,.24);
        border-radius: 10px;
    }

    @media(max-width: 768px) {
        .custom-sidebar {
            width: 220px !important;
            min-width: 220px !important;
            max-width: 220px !important;
        }
    }
</style>

<ul class="navbar-nav sidebar sidebar-dark accordion custom-sidebar" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
        <div class="brand-mark mr-3">
            <i class="fas fa-seedling"></i>
        </div>
        <div class="text-left">
            <div class="brand-title">SUBSIDI PUPUK</div>
            <div class="brand-subtitle">Dinas Pertanian</div>
        </div>
    </a>

    <div class="sidebar-section-label">Menu Utama</div>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           href="{{ route('dashboard') }}">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
    </li>

    @if(auth()->user()->role === 'admin')

        @php
            $dataPenggunaAktif = request()->routeIs('penyuluh.*') || request()->routeIs('admin.*');
            $perhitunganAktif = request()->routeIs('anp.*') || request()->routeIs('vikor.*') || request()->routeIs('ranking.*');
            $pengaturanAktif = request()->routeIs('pengaturan.*')
                || request()->routeIs('pengaturan-periode-wilayah.*')
                || request()->routeIs('musim-tanam.*')
                || request()->routeIs('periode.*')
                || request()->routeIs('kecamatan.*')
                || request()->routeIs('alokasi-kecamatan.*')
                || request()->routeIs('poktan.*')
                || request()->routeIs('kriteria.*')
                || request()->routeIs('subkriteria.*')
                || request()->routeIs('jenis-pupuk.*');
        @endphp

        <li class="nav-item">
            <a class="nav-link {{ $dataPenggunaAktif ? 'active' : 'collapsed' }}"
               href="#"
               data-toggle="collapse"
               data-target="#menuDataPengguna"
               aria-expanded="{{ $dataPenggunaAktif ? 'true' : 'false' }}">
                <i class="fas fa-users-cog"></i>
                <span>Data Pengguna</span>
            </a>

            <div id="menuDataPengguna"
                 class="collapse {{ $dataPenggunaAktif ? 'show' : '' }}"
                 data-parent="#accordionSidebar">
                <div class="submenu-box">
                    <a class="collapse-item {{ request()->routeIs('penyuluh.*') ? 'active' : '' }}"
                       href="{{ route('penyuluh.index') }}">
                        <i class="fas fa-user-tie"></i>
                        Data Penyuluh
                    </a>
                    <a class="collapse-item {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                       href="{{ route('admin.index') }}">
                        <i class="fas fa-user-shield"></i>
                        Data Admin
                    </a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pengajuan.*') ? 'active' : '' }}"
               href="{{ route('pengajuan.index') }}">
                <i class="fas fa-inbox"></i>
                <span>Pengajuan Petani</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('alternatif-musim.*') ? 'active' : '' }}"
               href="{{ route('alternatif-musim.index') }}">
                <i class="fas fa-user-friends"></i>
                <span>Data Alternatif</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ $perhitunganAktif ? 'active' : 'collapsed' }}"
               href="#"
               data-toggle="collapse"
               data-target="#menuPerhitungan"
               aria-expanded="{{ $perhitunganAktif ? 'true' : 'false' }}">
                <i class="fas fa-calculator"></i>
                <span>Perhitungan</span>
            </a>

            <div id="menuPerhitungan"
                 class="collapse {{ $perhitunganAktif ? 'show' : '' }}"
                 data-parent="#accordionSidebar">
                <div class="submenu-box">
                    <a class="collapse-item {{ request()->routeIs('anp.*') ? 'active' : '' }}"
                       href="{{ route('anp.index') }}">
                        <i class="fas fa-project-diagram"></i>
                        Perhitungan ANP
                    </a>
                    <a class="collapse-item {{ request()->routeIs('vikor.*') ? 'active' : '' }}"
                       href="{{ route('vikor.index') }}">
                        <i class="fas fa-chart-line"></i>
                        Perhitungan VIKOR
                    </a>
                    <a class="collapse-item {{ request()->routeIs('ranking.*') ? 'active' : '' }}"
                       href="{{ route('ranking.index') }}">
                        <i class="fas fa-trophy"></i>
                        Hasil Ranking
                    </a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}"
               href="{{ route('laporan.index') }}">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ $pengaturanAktif ? 'active' : '' }}"
               href="{{ route('pengaturan.index') }}">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </li>

    @else

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('poktan.*') ? 'active' : '' }}"
               href="{{ route('poktan.index') }}">
                <i class="fas fa-users"></i>
                <span>Data Poktan</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pengajuan.*') ? 'active' : '' }}"
               href="{{ route('pengajuan.index') }}">
                <i class="fas fa-inbox"></i>
                <span>Pengajuan Petani</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('alternatif-musim.*') ? 'active' : '' }}"
               href="{{ route('alternatif-musim.index') }}">
                <i class="fas fa-user-friends"></i>
                <span>Petani Binaan</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('ranking.*') ? 'active' : '' }}"
               href="{{ route('ranking.index') }}">
                <i class="fas fa-trophy"></i>
                <span>Hasil Prioritas</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}"
               href="{{ route('laporan.index') }}">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
            </a>
        </li>

    @endif

</ul>
