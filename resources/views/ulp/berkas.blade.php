@extends('layouts.ulp')
@section('title', 'Data Berkas PRR')

@section('content')

<div class="page-heading" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">

    <div>
        <h1>Data Berkas PRR</h1>
        <p>{{ auth()->user()->ulp_label }}</p>
    </div>

    <a href="{{ route('ulp.dashboard') }}" class="btn btn-primary">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Dashboard
    </a>

</div>

@include('components.tabel-berkas', [
    'berkas'        => $berkas,
    'totalTagihan'  => $totalTagihan,
    'ulp'           => $ulp,
    'canImport'     => false,
    'storeRoute'    => 'ulp.berkas.store',
    'updateRoute'   => 'ulp.berkas.update',
    'deleteRoute'   => 'ulp.berkas.destroy',
    'currentStatus' => $currentStatus,
])

@endsection
