@extends('layouts.app')
@section('title','Pengaturan')

@section('content')
<style>
.settings-page{padding-bottom:28px}.settings-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.settings-card{display:flex;flex-direction:column;min-height:190px;padding:22px;background:#fff;border:1px solid #edf0f4;border-radius:14px;color:inherit!important;text-decoration:none!important;box-shadow:0 4px 14px rgba(15,23,42,.05);transition:.2s ease}.settings-card:hover{transform:translateY(-2px);border-color:#bbf7d0;box-shadow:0 10px 25px rgba(22,163,74,.10)}.settings-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#ecfdf5;color:#16a34a;font-size:1.1rem;margin-bottom:16px}.settings-title{color:#1f2937;font-weight:700;font-size:1rem;margin-bottom:8px}.settings-desc{color:#6b7280;font-size:.84rem;line-height:1.55;margin-bottom:18px}.settings-meta{margin-top:auto;color:#16a34a;font-size:.78rem;font-weight:700}@media(max-width:1100px){.settings-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:650px){.settings-grid{grid-template-columns:1fr}}
</style>

<div class="container-fluid settings-page">
    <div class="app-page-header"><h1 class="app-page-title">Pengaturan</h1></div>

    <div class="settings-grid">
        <a href="{{ route('pengaturan-periode-wilayah.index') }}" class="settings-card">
            <div class="settings-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="settings-title">Periode & Wilayah</div>
            <div class="settings-desc">Musim Tanam, Tahun, Kecamatan, dan kuota penerima.</div>
            <div class="settings-meta">{{ $ringkasan['musim_tanam'] }} musim · {{ $ringkasan['periode'] }} tahun · {{ $ringkasan['kecamatan'] }} kecamatan</div>
        </a>

        <a href="{{ route('poktan.index') }}" class="settings-card">
            <div class="settings-icon"><i class="fas fa-users"></i></div>
            <div class="settings-title">Data Poktan</div>
            <div class="settings-desc">Kelola kelompok tani dan Penyuluh pembina.</div>
            <div class="settings-meta">{{ $ringkasan['poktan'] }} Poktan</div>
        </a>

        <a href="{{ route('kriteria.index') }}" class="settings-card">
            <div class="settings-icon"><i class="fas fa-list-alt"></i></div>
            <div class="settings-title">Data Kriteria</div>
            <div class="settings-desc">Kelola kriteria C1 sampai C5.</div>
            <div class="settings-meta">{{ $ringkasan['kriteria'] }} kriteria</div>
        </a>

        <a href="{{ route('subkriteria.index') }}" class="settings-card">
            <div class="settings-icon"><i class="fas fa-list-ul"></i></div>
            <div class="settings-title">Data Subkriteria</div>
            <div class="settings-desc">Kelola pilihan dan nilai subkriteria.</div>
            <div class="settings-meta">{{ $ringkasan['subkriteria'] }} subkriteria</div>
        </a>

        <a href="{{ route('jenis-pupuk.index') }}" class="settings-card">
            <div class="settings-icon"><i class="fas fa-seedling"></i></div>
            <div class="settings-title">Jenis Pupuk</div>
            <div class="settings-desc">Kelola jenis pupuk yang tersedia pada pengajuan.</div>
            <div class="settings-meta">{{ $ringkasan['jenis_pupuk'] }} jenis aktif</div>
        </a>

        <a href="{{ route('audit.index') }}" class="settings-card">
            <div class="settings-icon"><i class="fas fa-history"></i></div>
            <div class="settings-title">Riwayat Aktivitas</div>
            <div class="settings-desc">Lihat dan kelola riwayat aktivitas sistem.</div>
            <div class="settings-meta">{{ $ringkasan['audit_log'] }} aktivitas</div>
        </a>
    </div>
</div>
@endsection
