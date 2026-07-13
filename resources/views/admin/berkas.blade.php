@extends('layouts.admin')
@section('title', 'Data Berkas PRR')

@section('content')

<div class="page-heading">
    <h1>{{ $judul }}</h1>
    <p>Data Berkas Piutang Ragu-Ragu (PRR)</p>
</div>

<div class="flex" style="justify-content:end; gap:10px; margin-bottom:16px;">

    @if($canImport)
        <button
            type="button"
            class="btn btn-primary"
            onclick="openModal('modal-import')">

            <i class="fas fa-file-import"></i>
            Import Excel

        </button>
    @endif

    <a
        href="{{ route('admin.export', ['ulp'=>$ulp]) }}"
        class="btn btn-success">

        <i class="fas fa-file-excel"></i>
        Export Excel

    </a>

</div>

@include('components.tabel-berkas', [
    'berkas'        => $berkas,
    'totalTagihan'  => $totalTagihan,
    'ulp'           => $ulp,
    'canImport'     => $canImport,
    'canManage'     => $canManage,
    'storeRoute'    => 'admin.berkas.store',
    'updateRoute'   => 'admin.berkas.update',
    'deleteRoute'   => 'admin.berkas.destroy',
    'currentStatus' => $currentStatus,
])

@endsection

@push('modals')
<div class="modal-overlay" id="modal-import">
    <div class="modal" style="max-width:450px">
        <div class="modal__header">
            <div class="modal__title">📥 Import Data dari Excel</div>
            <button class="modal__close" onclick="closeModal('modal-import')">✕</button>
        </div>

        <form method="POST"
        action="{{ route('admin.import') }}"
        enctype="multipart/form-data">

        @csrf

        <div class="form-group">

            <label>File Excel (.xlsx / .xls / .csv)</label>

            <label class="upload-label">

                <input
                    type="file"
                    name="file"
                    accept=".xlsx,.xls,.csv"
                    onchange="showFileName(this,'excel_name')"
                    required>

                <span>📂 Pilih File Excel</span>

                <span
                    id="excel_name"
                    style="font-size:12px;color:#555">

                    Belum ada file

                </span>

            </label>

        </div>

        <p style="font-size:12px;color:#6B7A99;margin-bottom:16px">

            ℹ Kolom Excel harus berurutan sebagai berikut:

            <br><br>

            <b>
                nomor_unit,
                id_pelanggan,
                nama_pelanggan,
                tarif,
                daya,
                lembar,
                tagihan,
                tanggal_periksa,
                koordinat_x,
                koordinat_y,
                kondisi_lapangan
            </b>

            <br><br>

            Nomor Unit akan digunakan sistem untuk menentukan ULP secara otomatis.

        </p>

        <div style="display:flex;gap:10px">

            <button
                type="submit"
                class="btn btn-yellow">

                📥 Import Sekarang

            </button>

            <button
                type="button"
                class="btn btn-outline"
                onclick="closeModal('modal-import')">

                Batal

            </button>

        </div>

    </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
function showFileName(input, spanId) {
    document.getElementById(spanId).textContent =
        input.files.length ? input.files[0].name : 'Belum ada file';
}
</script>
@endpush
