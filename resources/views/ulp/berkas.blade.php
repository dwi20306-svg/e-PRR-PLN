@extends('layouts.ulp')
@section('title', 'Data Berkas PRR')

@section('content')

<div class="page-heading">
    <h1>Data Berkas PRR</h1>
    <p>{{ auth()->user()->ulp_label }}</p>
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