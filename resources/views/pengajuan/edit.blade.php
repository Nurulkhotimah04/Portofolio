@extends('layouts.app')
@section('title','Perbaiki Pengajuan')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h3 mb-1 text-gray-800">Perbaiki Pengajuan</h1></div><a href="{{ route('pengajuan.show',$pengajuan) }}" class="btn btn-light border">Kembali</a></div>
    @if($pengajuan->catatan_admin)<div class="alert alert-info"><strong>Catatan Admin:</strong> {{ $pengajuan->catatan_admin }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('pengajuan.update',$pengajuan) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('pengajuan._form')
        <div class="text-right"><button class="btn btn-success">Simpan & Kirim Ulang</button></div>
    </form>
</div>
@endsection
