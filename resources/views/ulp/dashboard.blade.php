@extends('layouts.ulp')

@section('title', 'Dashboard ULP')

@section('content')

<div class="page-heading">
    <h1>{{ auth()->user()->ulp_label }}</h1>

    <p>
        Dashboard Piutang Ragu-Ragu (PRR)
    </p>
</div>

{{-- ================= CARD ================= --}}

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:25px;">

    <div class="card" style="padding:20px">
        <small>Total Berkas PRR</small>
        <h2>{{ number_format($grandBerkas) }}</h2>
    </div>

    <div class="card" style="padding:20px">
        <small>Total Tagihan</small>
        <h2>
            Rp {{ number_format($grandTotal,0,',','.') }}
        </h2>
    </div>

    <div class="card" style="padding:20px">
        <small>Berkas Lengkap</small>
        <h2 style="color:#28a745">
            {{ $grandLengkap }}
        </h2>
    </div>

    <div class="card" style="padding:20px">
        <small>Belum Lengkap</small>
        <h2 style="color:#ffc107">
            {{ $grandBelumLengkap }}
        </h2>
    </div>

    <div class="card" style="padding:20px">
        <small>Belum Upload</small>
        <h2 style="color:#dc3545">
            {{ $grandBelumUpload }}
        </h2>
    </div>

</div>

{{-- ================= INFORMASI ================= --}}

<div class="card">

    <div class="card__header">
        <div class="card__title">
            Ringkasan Data {{ auth()->user()->ulp_label }}
        </div>
    </div>

    <div style="padding:20px;line-height:1.8">

        <p>
            Selamat datang di aplikasi <b>e-PRR</b>.
        </p>

        <p>
            Dashboard ini menampilkan ringkasan jumlah berkas Piutang Ragu-Ragu (PRR)
            yang menjadi tanggung jawab {{ auth()->user()->ulp_label }}.
        </p>

        <ul style="margin-left:20px">
            <li>Total berkas PRR yang dikelola.</li>
            <li>Total nominal tagihan.</li>
            <li>Jumlah berkas yang sudah lengkap.</li>
            <li>Jumlah berkas yang masih belum lengkap.</li>
            <li>Jumlah berkas yang belum memiliki dokumen.</li>
        </ul>

        <div style="margin-top:20px">

            <a href="{{ route('ulp.berkas.index') }}" class="btn btn-primary">
                <i class="fa-solid fa-folder-open"></i>
                Lihat Data Berkas
            </a>

        </div>

    </div>

</div>

@endsection
