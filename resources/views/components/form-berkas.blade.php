
@php
    $isEdit = isset($berkas) && $berkas;
    $sufiks = $isEdit ? $berkas?->id : 'new';

    // 1. AMBIL DATA ULP DENGAN DETEKSI KATA KUNCI (ANTI-ERROR)
    $userUlpRaw = auth()->user()->ulp_name ?? auth()->user()->ulp ?? '';
    $userUlpLower = strtolower($userUlpRaw);

    $autoUnit = '';
    if (str_contains($userUlpLower, 'merduati')) {
        $autoUnit = '11110';
    } elseif (str_contains($userUlpLower, 'bieng') || str_contains($userUlpLower, 'keudeu')) {
        $autoUnit = '11111';
    } elseif (str_contains($userUlpLower, 'lambaro')) {
        $autoUnit = '11112';
    } elseif (str_contains($userUlpLower, 'jantho')) {
        $autoUnit = '11113';
    } elseif (str_contains($userUlpLower, 'sabang')) {
        $autoUnit = '11114';
    } elseif (str_contains($userUlpLower, 'kuala') || str_contains($userUlpLower, 'syiah')) {
        $autoUnit = '11115';
    }

    // Jika sedang edit, pakai nomor unit lama yang ada di database
    $finalUnit = $isEdit ? $berkas->nomor_unit : $autoUnit;

    // Cek Role Admin secara aman
    $isAdmin = method_exists(auth()->user(), 'isAdmin')
        ? auth()->user()->isAdmin()
        : (in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'administrator']));
@endphp

<div style="display: block !important; width: 100% !important; float: none !important; clear: both !important; text-align: left !important; box-sizing: border-box !important;">

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" style="display: block !important; width: 100% !important;">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <input type="hidden" name="ulp" value="{{ $ulp }}">

        {{-- ── SEKSI 1: DATA PELANGGAN ── --}}
        <div style="display: block !important; width: 100% !important; margin-bottom: 25px !important;">
            <div style="font-weight: 700; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; color: #1e293b; text-transform: uppercase;">
                Data Pelanggan
            </div>

            <div style="display: grid !important; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important; gap: 16px !important; width: 100% !important;">

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Nomor Unit *</label>
                    @if($isAdmin)
                        @php
                        $nomorUnitMap = [
                            'ulp_merduati' => '11110',
                            'ulp_keudeu_bieng' => '11111',
                            'ulp_lambaro' => '11112',
                            'ulp_jantho' => '11113',
                            'ulp_sabang' => '11114',
                            'ulp_syiah_kuala' => '11115',
                        ];

                        $nomorUnit = $nomorUnitMap[$ulp] ?? '';
                        @endphp

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $nomorUnit }}"
                            readonly
                        >

                        <input
                            type="hidden"
                            name="nomor_unit"
                            value="{{ $nomorUnit }}"
                        >
                    @else
                        <input type="text" name="nomor_unit" value="{{ old('nomor_unit', $finalUnit) }}" readonly required
                               style="width: 100%; height: 40px; padding: 0 10px; border-radius: 6px; font-size: 14px; background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; cursor: not-allowed;">
                    @endif
                    @error('nomor_unit') <div style="color: #ef4444; font-size: 11px;">{{ $message }}</div> @enderror
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">ID Pelanggan *</label>
                    <input type="text" name="id_pelanggan" value="{{ old('id_pelanggan', $berkas?->id_pelanggan ?? '') }}" required
                           style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 14px; background: #fff;">
                    @error('id_pelanggan') <div style="color: #ef4444; font-size: 11px;">{{ $message }}</div> @enderror
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Nama Pelanggan *</label>
                    <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $berkas?->nama_pelanggan ?? '') }}" required
                           style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 14px; background: #fff;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Tarif *</label>
                    <input type="text" name="tarif" value="{{ old('tarif', $berkas?->tarif ?? '') }}" placeholder="cth: R1, S2" required
                           style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 14px; background: #fff;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Daya (VA) *</label>
                    <input type="number" name="daya" value="{{ old('daya', $berkas?->daya ?? '') }}" required
                           style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 14px; background: #fff;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Lembar *</label>
                    <input type="number" name="lembar" value="{{ old('lembar', $berkas?->lembar ?? '') }}" required
                           style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 14px; background: #fff;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Tagihan (Rp) *</label>
                    <input type="number" name="tagihan" step="0.01" value="{{ old('tagihan', $berkas?->tagihan ?? '') }}" required
                           style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 14px; background: #fff;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Tanggal Periksa *</label>
                    <input type="date" name="tanggal_periksa" value="{{ old('tanggal_periksa', $berkas?->tanggal_periksa?->format('Y-m-d') ?? '') }}"
                           style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 14px; background: #fff;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Kondisi Lapangan</label>
                    <select name="kondisi_lapangan" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; background: #fff; font-size: 14px;">
                        <option value="" disabled {{ old('kondisi_lapangan', $berkas?->kondisi_lapangan) === null ? 'selected' : '' }}>-- Pilih Kondisi --</option>
                        @foreach(['bongkar rampung', 'rata dengan tanah', 'sr seri', 'sr/ok belum rampung'] as $k)
                            <option value="{{ $k }}" {{ old('kondisi_lapangan', $berkas?->kondisi_lapangan) === $k ? 'selected' : '' }}>{{ ucwords($k) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ── SEKSI 2: TITIK KOORDINAT ── --}}
        <div style="display: block !important; width: 100% !important; margin-bottom: 25px !important;">
            <div style="font-weight: 700; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; color: #1e293b; text-transform: uppercase;">
                Titik Koordinat
            </div>
            <div style="display: grid !important; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important; gap: 16px !important; width: 100% !important;">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Koordinat X (Longitude)</label>
                    <input type="number" name="koordinat_x" step="any" value="{{ old('koordinat_x', $berkas?->koordinat_x ?? '') }}"
                           style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 14px; background: #fff;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: 600; font-size: 13px; color: #475569;">Koordinat Y (Latitude)</label>
                    <input type="number" name="koordinat_y" step="any" value="{{ old('koordinat_y', $berkas?->koordinat_y ?? '') }}"
                           style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 14px; background: #fff;">
                </div>
            </div>
        </div>

        {{-- ── SEKSI 3: UPLOAD GAMBAR ── --}}
        <div style="display: block !important; width: 100% !important; margin-bottom: 25px !important;">
            <div style="font-weight: 700; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; color: #1e293b; text-transform: uppercase;">
                Upload Dokumen Gambar
            </div>
            <div style="display: grid !important; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)) !important; gap: 16px !important; width: 100% !important;">
                @php
                $gambarList = [
                    'gambar_tul_vi01'     => 'TUL VI-01 (Pemutusan)',
                    'gambar_tul_vi03'     => 'TUL VI-03 (Bongkar)',
                    'gambar_spk'          => 'SPK',
                    'gambar_berita_acara' => 'Berita Acara',
                    'gambar_pdp'          => 'PDP',
                    'gambar_tug10'        => 'TUG 10',
                    'gambar_invoice'      => 'Invoice',
                    'gambar_rumah'        => 'Rumah & Sisa Meteran',
                ];
                @endphp

                @foreach($gambarList as $field => $label)
                <div style="display: flex; flex-direction: column; gap: 4px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; border-radius: 8px;">
                    <label style="font-weight: 600; font-size: 12px; color: #334155; height: 32px; overflow: hidden;">{{ $label }}</label>

                    <label style="border: 2px dashed #cbd5e1; border-radius: 6px; padding: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; background: #fff; text-align: center; min-height: 90px;">
                        <input type="file" name="{{ $field }}" accept="image/*" style="display: none;"
                               onchange="previewImg(this, '{{ $field }}_{{ $sufiks }}_preview')">
                        <span style="font-size: 11px; color: #64748b;">📷 Pilih Gambar</span>

                        @if($isEdit && $berkas?->$field)
                            <img id="{{ $field }}_{{ $sufiks }}_preview" src="{{ Storage::url($berkas->$field) }}"
                                 style="width: 100%; height: 60px; object-fit: cover; margin-top: 6px; border-radius: 4px;">
                        @else
                            <img id="{{ $field }}_{{ $sufiks }}_preview" style="display: none; width: 100%; height: 60px; object-fit: cover; margin-top: 6px; border-radius: 4px;">
                        @endif
                    </label>

                    @if($isEdit)
                        <div style="font-size: 10px; text-align: center; font-weight: 500; color: {{ $berkas?->$field ? '#10b981' : '#ef4444' }}">
                            {{ $berkas?->$field ? '✓ Terupload' : '✗ Kosong' }}
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── SEKSI 4: UPLOAD PDF ── --}}
        <div style="display: block !important; width: 100% !important; margin-bottom: 25px !important;">
            <div style="font-weight: 700; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; color: #1e293b; text-transform: uppercase;">
                Upload PDF Hasil Scan Seluruh Dokumen
            </div>
            <div style="display: block !important; width: 100% !important; background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px;">
                <label style="border: 2px dashed #cbd5e1; border-radius: 6px; padding: 12px; display: flex; align-items: center; gap: 10px; cursor: pointer; background: #fff;">
                    <input type="file" name="pdf_scan" accept=".pdf" style="display: none;"
                           onchange="showFileName(this, 'pdf_name_{{ $sufiks }}')">
                    <span style="font-size: 12px; font-weight: 600; color: #475569; background: #f1f5f9; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px;">📄 Pilih PDF</span>
                    <span id="pdf_name_{{ $sufiks }}" style="font-size: 13px; color: #64748b;">
                        {{ $isEdit && $berkas?->pdf_scan ? basename($berkas->pdf_scan) : 'Belum ada file terpilih' }}
                    </span>
                </label>
                @if($isEdit)
                    <div style="margin-top: 6px; font-size: 11px; font-weight: 500; color: {{ $berkas?->pdf_scan ? '#10b981' : '#ef4444' }}">
                        {{ $berkas?->pdf_scan ? '✓ PDF sudah diupload di server' : '✗ PDF belum diupload' }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div style="display: flex !important; justify-content: flex-end !important; gap: 10px !important; margin-top: 25px !important; padding-top: 15px !important; border-top: 1px solid #e2e8f0 !important; width: 100% !important;">
            <button type="button" class="btn btn-outline" style="padding: 10px 20px; border-radius: 6px; font-size: 14px;"
                    onclick="closeModal(this.closest('.modal-overlay').id)">
                Batal
            </button>
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 600;">
                {{ $isEdit ? '💾 Simpan Perubahan' : '➕ Tambah Berkas' }}
            </button>
        </div>
    </form>
</div>

<script>
if (typeof previewImg !== 'function') {
    function previewImg(input, previewId) {
        const preview = document.getElementById(previewId);
        if (!preview) return;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
}
if (typeof showFileName !== 'function') {
    function showFileName(input, spanId) {
        const span = document.getElementById(spanId);
        if (!span) return;
        span.textContent = input.files[0] ? input.files[0].name : 'Belum ada file terpilih';
    }
}
</script>
