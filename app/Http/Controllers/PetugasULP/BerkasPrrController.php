<?php

namespace App\Http\Controllers\PetugasULP;

use App\Http\Controllers\Controller;
use App\Models\BerkasPrr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BerkasPrrController extends Controller
{
    private array $gambarFields = [
        'gambar_tul_vi01', 'gambar_tul_vi03', 'gambar_spk', 'gambar_berita_acara',
        'gambar_pdp', 'gambar_tug10', 'gambar_invoice', 'gambar_rumah',
    ];

    /**
     * Halaman tabel berkas internal ULP mandiri (Screenshot 5).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $ulp = $user->ulp_name;

        $search = $request->get('search');

        $query = BerkasPrr::where('ulp', $ulp);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id_pelanggan', 'like', "%{$search}%")
                ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                ->orWhere('nomor_unit', 'like', "%{$search}%");
            });
        }

        $totalTagihan = (clone $query)->sum('tagihan');

        $berkas = $query->latest()->paginate(20)->withQueryString();

        $currentStatus = $this->hitungStatusUlp($berkas);

        return view('ulp.berkas', compact(
            'berkas',
            'ulp',
            'totalTagihan',
            'currentStatus'
        ));
    }

    /**
     * Simpan data berkas baru (Otomatis mengunci folder upload sesuai ULP petugas).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $ulp  = $user->ulp_name;

        $data = $this->validateBerkas($request);
        $data['ulp'] = $ulp;
        $data['user_id'] = $user->id;

        foreach ($this->gambarFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)
                    ->store("berkas/{$ulp}/{$field}", 'public');
            }
        }

        if ($request->hasFile('pdf_scan')) {
            $data['pdf_scan'] = $request->file('pdf_scan')
                ->store("berkas/{$ulp}/pdf", 'public');
        }

        BerkasPrr::create($data);

        return back()->with('success', 'Berkas PRR berhasil ditambahkan.');
    }

    /**
     * Update data berkas internal ULP (Proteksi hak akses lintas ULP).
     */
    public function update(Request $request, BerkasPrr $berkasPrr)
    {
        if ($berkasPrr->ulp !== Auth::user()->ulp_name) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah berkas ULP lain.');
        }

        $data = $this->validateBerkas($request, $berkasPrr->id);

        foreach ($this->gambarFields as $field) {
            if (!$request->hasFile($field)) {
                continue;
            }

            if ($berkasPrr->$field) {
                Storage::disk('public')->delete($berkasPrr->$field);
            }

            $data[$field] = $request->file($field)
                ->store("berkas/{$berkasPrr->ulp}/{$field}", 'public');
        }

        if ($request->hasFile('pdf_scan')) {
            if ($berkasPrr->pdf_scan) {
                Storage::disk('public')->delete($berkasPrr->pdf_scan);
            }

            $data['pdf_scan'] = $request->file('pdf_scan')
                ->store("berkas/{$berkasPrr->ulp}/pdf", 'public');
        }

        $berkasPrr->update($data);

        return back()->with('success', 'Berkas PRR berhasil diperbarui.');
    }

    /**
     * Hapus data berkas internal ULP.
     */
    public function destroy(BerkasPrr $berkasPrr)
    {
        if ($berkasPrr->ulp !== Auth::user()->ulp_name) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus berkas ULP lain.');
        }

        foreach ([...$this->gambarFields, 'pdf_scan'] as $field) {
            if ($berkasPrr->$field) {
                Storage::disk('public')->delete($berkasPrr->$field);
            }
        }

        $berkasPrr->delete();

        return back()->with('success', 'Berkas PRR berhasil dihapus.');
    }

    private function validateBerkas(Request $request, $ignoreId = null): array
    {
            return $request->validate(

            [
                'nomor_unit'       => 'required|string|in:11110,11111,11112,11113,11114,11115',
                'id_pelanggan'     => 'required|string|max:50|unique:berkas_prr,id_pelanggan,' . $ignoreId,
                'nama_pelanggan'   => 'required|string|max:150',
                'tarif'            => 'required|string|max:20',
                'daya'             => 'required|integer|min:1',
                'lembar'           => 'required|integer|min:1',
                'tagihan'          => 'required|numeric|min:0',
                'tanggal_periksa'  => 'required|date',

                'koordinat_x'      => 'nullable|numeric',
                'koordinat_y'      => 'nullable|numeric',

                'kondisi_lapangan' => 'nullable|in:bongkar rampung,rata dengan tanah,sr seri,sr/ok belum rampung',

                'gambar_tul_vi01'     => 'nullable|image|max:5120',
                'gambar_tul_vi03'     => 'nullable|image|max:5120',
                'gambar_spk'          => 'nullable|image|max:5120',
                'gambar_berita_acara' => 'nullable|image|max:5120',
                'gambar_pdp'          => 'nullable|image|max:5120',
                'gambar_tug10'        => 'nullable|image|max:5120',
                'gambar_invoice'      => 'nullable|image|max:5120',
                'gambar_rumah'        => 'nullable|image|max:5120',

                'pdf_scan'            => 'nullable|mimes:pdf|max:10240',
            ],

            [

                'nomor_unit.required'      => 'Nomor Unit wajib diisi.',
                'nomor_unit.in'            => 'Nomor Unit tidak valid.',

                'id_pelanggan.required'    => 'ID Pelanggan wajib diisi.',
                'id_pelanggan.unique'      => 'ID Pelanggan sudah terdaftar.',

                'nama_pelanggan.required'  => 'Nama Pelanggan wajib diisi.',

                'tarif.required'           => 'Tarif wajib diisi.',

                'daya.required'            => 'Daya wajib diisi.',
                'daya.integer'             => 'Daya harus berupa angka.',
                'daya.min'                 => 'Daya minimal 1 VA.',

                'lembar.required'          => 'Jumlah lembar wajib diisi.',
                'lembar.integer'           => 'Jumlah lembar harus berupa angka.',

                'tagihan.required'         => 'Tagihan wajib diisi.',
                'tagihan.numeric'          => 'Tagihan harus berupa angka.',

                'tanggal_periksa.required' => 'Tanggal Periksa wajib diisi.',
                'tanggal_periksa.date'     => 'Format tanggal tidak valid.',

                'koordinat_x.numeric'      => 'Koordinat X harus berupa angka.',
                'koordinat_y.numeric'      => 'Koordinat Y harus berupa angka.',

                'gambar_tul_vi01.image'     => 'File TUL VI-01 harus berupa gambar.',
                'gambar_tul_vi03.image'     => 'File TUL VI-03 harus berupa gambar.',
                'gambar_spk.image'          => 'File SPK harus berupa gambar.',
                'gambar_berita_acara.image' => 'File Berita Acara harus berupa gambar.',
                'gambar_pdp.image'          => 'File PDP harus berupa gambar.',
                'gambar_tug10.image'        => 'File TUG 10 harus berupa gambar.',
                'gambar_invoice.image'      => 'File Invoice harus berupa gambar.',
                'gambar_rumah.image'        => 'File Gambar Rumah harus berupa gambar.',

                'pdf_scan.mimes'            => 'File scan harus berformat PDF.',
                'pdf_scan.max'              => 'Ukuran PDF maksimal 10 MB.',

            ]

        );
    }

    private function hitungStatusUlp($berkas): string
    {
        if ($berkas->isEmpty()) return 'none';
        $lengkap = 0; $belumLengkap = 0;

        foreach ($berkas as $item) {
            $terisi = collect($this->gambarFields)->filter(fn($field) => !empty($item->$field))->count();
            if ($terisi == count($this->gambarFields)) { $lengkap++; }
            elseif ($terisi > 0) { $belumLengkap++; }
        }

        if ($lengkap == $berkas->count()) return 'complete';
        if ($lengkap > 0 || $belumLengkap > 0) return 'partial';
        return 'none';
    }
}
