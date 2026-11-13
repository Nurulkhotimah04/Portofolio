@extends('layouts.app')
@section('title','Pengajuan Petani Baru')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-1 text-gray-800">Pengajuan Petani Baru</h1></div>
        <a href="{{ route('pengajuan.index') }}" class="btn btn-light border">Kembali</a>
    </div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('pengajuan.store') }}" enctype="multipart/form-data">
        @csrf
        @include('pengajuan._form')
        <div class="text-right"><a href="{{ route('pengajuan.index') }}" class="btn btn-light border">Batal</a><button class="btn btn-success ml-2"><i class="fas fa-paper-plane mr-1"></i>Kirim Pengajuan</button></div>
    </form>
</div>
@endsection
