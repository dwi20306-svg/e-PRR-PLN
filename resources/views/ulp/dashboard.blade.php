@extends('layouts.ulp')
@section('title', 'Dashboard ULP')

@section('content')

<div class="page-heading">
    <h1>{{ auth()->user()->ulp_label }}</h1>
    <p>Dashboard Piutang Ragu-Ragu (PRR)</p>
</div>

{{-- CARD RINGKASAN --}}
<div class="row mb-4">

    <div class="col-lg col-md-6 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">TOTAL BERKAS PRR</h6>
                <h1 class="fw-bold text-primary">{{ $grandBerkas }}</h1>
                <small>berkas ULP</small>
            </div>
        </div>
    </div>

    <div class="col-lg col-md-6 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">TOTAL TAGIHAN</h6>
                <h1 class="fw-bold text-primary">
                    Rp {{ number_format($grandTotal,0,',','.') }}
                </h1>
                <small>total tagihan</small>
            </div>
        </div>
    </div>

    <div class="col-lg col-md-6 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-success text-uppercase">BERKAS LENGKAP</h6>
                <h1 class="fw-bold text-success">{{ $grandLengkap }}</h1>
                <small>dokumen lengkap</small>
            </div>
        </div>
    </div>

    <div class="col-lg col-md-6 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-warning text-uppercase">BELUM LENGKAP</h6>
                <h1 class="fw-bold text-warning">{{ $grandBelumLengkap }}</h1>
                <small>sebagian upload</small>
            </div>
        </div>
    </div>

    <div class="col-lg col-md-6 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-danger text-uppercase">BELUM UPLOAD</h6>
                <h1 class="fw-bold text-danger">{{ $grandBelumUpload }}</h1>
                <small>belum ada dokumen</small>
            </div>
        </div>
    </div>

</div>

<a href="{{ route('ulp.berkas') }}" class="btn btn-primary">
    📋 Lihat Data Berkas
</a>
@endsection