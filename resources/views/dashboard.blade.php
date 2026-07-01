@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.ulp')

@section('title', 'Dashboard')

@section('content')

<div class="page-heading">
    <h1>Dashboard e-PRR</h1>

    <p>
        @if($isAdmin)
            Rekap seluruh ULP PLN UP3 Banda Aceh
        @else
            Rekap {{ $rekapData[$userUlp]['label'] ?? '' }}
        @endif
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
        <h2>Rp {{ number_format($grandTotal,0,',','.') }}</h2>
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

{{-- ================= TABEL ================= --}}

<div class="card">

    <div class="card__header">
        <div class="card__title">
            Rekap Data PRR per ULP
        </div>
    </div>

    <div class="table-wrap">

        <table class="tbl">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Unit</th>
                    <th>Nama ULP</th>
                    <th>Jumlah Berkas</th>
                    <th>Lengkap</th>
                    <th>Belum Lengkap</th>
                    <th>Belum Upload</th>
                    <th>Total Tagihan</th>
                </tr>
            </thead>

            <tbody>

                @foreach($rekapData as $data)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $data['nomor_unit'] }}</td>

                    <td>{{ $data['label'] }}</td>

                    <td>{{ $data['jumlah_berkas'] }}</td>

                    <td style="color:green;font-weight:bold">
                        {{ $data['lengkap'] }}
                    </td>

                    <td style="color:orange;font-weight:bold">
                        {{ $data['belum_lengkap'] }}
                    </td>

                    <td style="color:red;font-weight:bold">
                        {{ $data['belum_upload'] }}
                    </td>

                    <td>
                        Rp {{ number_format($data['total_tagihan'],0,',','.') }}
                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

@if($isAdmin)

<div style="margin-top:20px">

    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
        Kelola Data Berkas
    </a>

</div>

@else

<div style="margin-top:20px">

    <a href="{{ route('ulp.berkas') }}" class="btn btn-primary">
        Lihat Data Berkas Saya
    </a>

</div>

@endif

@endsection