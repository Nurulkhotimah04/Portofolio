@extends('layouts.app')
@section('title', 'Tambah Alternatif')
@section('content')
<div class="container-fluid">
<a href="{{ route('alternatif-musim.index') }}" class="app-back-link"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Alternatif</a>
<div class="app-page-header"><div><h1 class="app-page-title">Tambah Alternatif</h1></div></div>
<form method="POST" action="{{ route('alternatif-musim.store') }}" enctype="multipart/form-data">@csrf @include('alternatif_musim._form')<div class="text-right"><a href="{{ route('alternatif-musim.index') }}" class="btn btn-light border">Batal</a><button class="btn btn-success ml-2">Simpan Alternatif</button></div></form>
</div>
@endsection
