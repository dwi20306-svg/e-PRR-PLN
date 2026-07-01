{{--
    @param Collection $berkas
    @param float      $totalTagihan
    @param string     $ulp
    @param bool       $canImport
    @param string     $storeRoute
    @param string     $updateRoute
    @param string     $deleteRoute
    @param string     $currentStatus
--}}

<div class="card">
    <div class="card__header">
        <div class="card__title">Daftar Berkas PRR (Piutang Ragu-Ragu)</div>

        {{-- Import Excel (admin saja) --}}
        @if($canImport)
        <button class="btn btn-yellow btn-sm" onclick="openModal('modal-import')">
            📥 Import Excel
        </button>
        @endif

        {{-- Tombol Tambah --}}
        <button class="btn btn-primary btn-sm" onclick="openModal('modal-tambah')">
            ➕ Tambah Berkas
        </button>
    </div>

    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Unit</th>
                    <th>ID Pelanggan</th>
                    <th>Nama Pelanggan</th>
                    <th>Tarif</th>
                    <th>Daya (VA)</th>
                    <th>Lembar</th>
                    <th>Tagihan (Rp)</th>
                    <th>Tgl Periksa</th>
                    <th>Kondisi Lapangan</th>
                    <th>Status Berkas</th>
                    <th>Foto Berkas</th>
                    <th>Koordinat</th>
                    <th>PDF Scan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($berkas as $i => $b)
                @php
                    // Hitung status otomatis berdasarkan gambar yang terupload
                    $gambarFields = [
                        'gambar_tul_vi01', 'gambar_tul_vi03', 'gambar_spk',
                        'gambar_berita_acara', 'gambar_pdp', 'gambar_tug10',
                        'gambar_invoice', 'gambar_rumah',
                    ];
                    $totalGambar  = count($gambarFields);
                    $terisi       = collect($gambarFields)->filter(fn($f) => !empty($b->$f))->count();
                    $statusOtomatis = $terisi === 0
                        ? 'belum upload'
                        : ($terisi < $totalGambar ? 'belum lengkap' : 'lengkap');

                    $stClass = match($statusOtomatis) {
                        'lengkap'       => 'complete',
                        'belum lengkap' => 'partial',
                        default         => 'none',
                    };
                    $stLabel = match($statusOtomatis) {
                        'lengkap'       => '✓ Lengkap',
                        'belum lengkap' => '⚠ Belum Lengkap',
                        default         => '✗ Belum Upload',
                    };
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $b->nomor_unit }}</td>
                    <td>{{ $b->id_pelanggan }}</td>
                    <td style="white-space:nowrap">{{ $b->nama_pelanggan }}</td>
                    <td>{{ $b->tarif }}</td>
                    <td>{{ number_format($b->daya) }}</td>
                    <td>{{ $b->lembar }}</td>
                    <td style="white-space:nowrap">Rp {{ number_format($b->tagihan, 0, ',', '.') }}</td>
                    <td style="white-space:nowrap">{{ $b->tanggal_periksa->format('d/m/Y') }}</td>

                    {{-- Kondisi Lapangan --}}
                    <td style="white-space:nowrap">
                        @if($b->kondisi_lapangan)
                            <span style="font-size:12px">{{ ucwords($b->kondisi_lapangan) }}</span>
                        @else
                            <span style="color:#ccc">—</span>
                        @endif
                    </td>

                    {{-- Status Berkas (otomatis dari gambar) --}}
                    <td>
                        <span class="status-badge {{ $stClass }}" style="font-size:11px;white-space:nowrap">
                            {{ $stLabel }}
                        </span>
                    </td>

                    {{-- Foto Berkas (tombol popup) --}}
                    <td>
                        <button class="btn btn-outline btn-sm"
                                onclick="openModal('modal-foto-{{ $b->id }}')">
                            🖼 Lihat Foto
                        </button>
                    </td>

                    {{-- Koordinat --}}
                    <td style="white-space:nowrap;font-size:12px">
                        @if($b->koordinat_x && $b->koordinat_y)
                            X: {{ $b->koordinat_x }}<br>
                            Y: {{ $b->koordinat_y }}
                        @else
                            <span style="color:#ccc">—</span>
                        @endif
                    </td>

                    {{-- PDF --}}
                    <td>
                        @if($b->pdf_scan)
                            <a href="{{ Storage::url($b->pdf_scan) }}" target="_blank"
                               class="btn btn-outline btn-sm">📄 PDF</a>
                        @else
                            <span style="color:#ccc;font-size:11px">—</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td>
                        <div class="actions">
                            <button class="btn btn-yellow btn-sm"
                                    onclick="openModal('modal-edit-{{ $b->id }}')">
                                ✏ Edit
                            </button>
                            <form method="POST"
                                  action="{{ route($deleteRoute, $b->id) }}"
                                  onsubmit="return confirm('Yakin hapus berkas ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" style="text-align:center;padding:40px;color:#9CA3AF">
                        <div style="font-size:32px;margin-bottom:8px">📂</div>
                        Belum ada data berkas PRR.<br>
                        <span style="font-size:12px">Klik "Tambah Berkas" untuk memulai.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="7" style="text-align:right;padding-right:12px">
                        <strong>TOTAL TAGIHAN</strong>
                    </td>
                    <td colspan="8">
                        <strong>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ══ MODAL TAMBAH ══ --}}
<div class="modal-overlay" id="modal-tambah">
    <div class="modal" style="width:min(800px,96vw)">
        <div class="modal__header">
            <div class="modal__title">➕ Tambah Berkas PRR</div>
            <button class="modal__close" onclick="closeModal('modal-tambah')">✕</button>
        </div>
        @include('components.form-berkas', [
            'action' => route($storeRoute),
            'method' => 'POST',
            'berkas' => null,
            'ulp'    => $ulp,
        ])
    </div>
</div>

{{-- ══ MODAL EDIT (satu per baris) ══ --}}
@foreach($berkas as $b)
<div class="modal-overlay" id="modal-edit-{{ $b->id }}">
    <div class="modal" style="width:min(800px,96vw)">
        <div class="modal__header">
            <div class="modal__title">✏ Edit Berkas — {{ $b->nama_pelanggan }}</div>
            <button class="modal__close" onclick="closeModal('modal-edit-{{ $b->id }}')">✕</button>
        </div>
        @include('components.form-berkas', [
            'action' => route($updateRoute, $b->id),
            'method' => 'PUT',
            'berkas' => $b,
            'ulp'    => $ulp,
        ])
    </div>
</div>
@endforeach

{{-- ══ MODAL FOTO BERKAS (satu per baris) ══ --}}
@foreach($berkas as $b)
@php
$fotoList = [
    'gambar_tul_vi01'     => 'TUL VI-01 — Surat Pemutusan',
    'gambar_tul_vi03'     => 'TUL VI-03 — Pembongkaran',
    'gambar_spk'          => 'SPK',
    'gambar_berita_acara' => 'Berita Acara',
    'gambar_pdp'          => 'PDP',
    'gambar_tug10'        => 'TUG 10',
    'gambar_invoice'      => 'Invoice',
    'gambar_rumah'        => 'Gambar Rumah & Bekas Bongkar',
];
$totalFoto  = count($fotoList);
$terIsiFoto = collect(array_keys($fotoList))->filter(fn($f) => !empty($b->$f))->count();
@endphp
<div class="modal-overlay" id="modal-foto-{{ $b->id }}">
    <div class="modal" style="width:min(900px,96vw)">
        <div class="modal__header">
            <div style="flex:1">
                <div class="modal__title">🖼 Foto Berkas — {{ $b->nama_pelanggan }}</div>
                <div style="font-size:12px;color:#6B7A99;margin-top:2px">
                    ID: {{ $b->id_pelanggan }} &nbsp;|&nbsp;
                    Terupload: {{ $terIsiFoto }}/{{ $totalFoto }} dokumen
                </div>
            </div>
            <button class="modal__close" onclick="closeModal('modal-foto-{{ $b->id }}')">✕</button>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
            @foreach($fotoList as $field => $label)
            <div style="border:1px solid #E5E7EB;border-radius:10px;overflow:hidden">
                {{-- Gambar atau placeholder --}}
                @if($b->$field)
                    <a href="{{ Storage::url($b->$field) }}" target="_blank">
                        <img src="{{ Storage::url($b->$field) }}"
                             style="width:100%;height:140px;object-fit:cover;display:block">
                    </a>
                @else
                    <div style="width:100%;height:140px;background:#F9FAFB;
                                display:flex;flex-direction:column;align-items:center;
                                justify-content:center;color:#D1D5DB">
                        <span style="font-size:28px">📷</span>
                        <span style="font-size:11px;margin-top:4px">Belum diupload</span>
                    </div>
                @endif
                {{-- Label --}}
                <div style="padding:8px 10px;background:#F9FAFB;border-top:1px solid #E5E7EB">
                    <div style="font-size:11px;font-weight:600;color:#374151">{{ $label }}</div>
                    @if($b->$field)
                        <div style="font-size:10px;color:#10B981;margin-top:2px">✓ Terupload</div>
                    @else
                        <div style="font-size:10px;color:#EF4444;margin-top:2px">✗ Belum ada</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- PDF --}}
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #E5E7EB;
                    display:flex;align-items:center;gap:12px">
            <span style="font-size:13px;font-weight:600;color:#374151">PDF Scan Seluruh Dokumen:</span>
            @if($b->pdf_scan)
                <a href="{{ Storage::url($b->pdf_scan) }}" target="_blank"
                   class="btn btn-outline btn-sm">📄 Buka PDF</a>
            @else
                <span style="font-size:12px;color:#EF4444">✗ Belum diupload</span>
            @endif
        </div>
    </div>
</div>
@endforeach
