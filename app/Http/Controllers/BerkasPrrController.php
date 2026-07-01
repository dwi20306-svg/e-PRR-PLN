<?php

namespace App\Http\Controllers;

use App\Models\BerkasPrr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BerkasPrrController extends Controller
{
    /**
     * Field gambar yang disimpan.
     */
    private array $gambarFields = [
        'gambar_tul_vi01',
        'gambar_tul_vi03',
        'gambar_spk',
        'gambar_berita_acara',
        'gambar_pdp',
        'gambar_tug10',
        'gambar_invoice',
        'gambar_rumah',
    ];

    /**
     * Halaman tabel berkas ULP.
     */
   public function ulpBerkas()
{
    $user   = Auth::user();
    $ulp    = $user->ulp_name;
    $berkas = BerkasPrr::where('ulp', $ulp)->latest()->get();

    $totalTagihan  = $berkas->sum('tagihan');
    $currentStatus = 'none';

    return view('ulp.berkas', compact('berkas', 'ulp', 'totalTagihan', 'currentStatus'));
}

    /**
     * Simpan data.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $ulp = $user->isAdmin()
            ? $request->ulp
            : $user->ulp_name;

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
     * Update data.
     */
    public function update(Request $request, BerkasPrr $berkasPrr)
    {
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
     * Hapus data.
     */
    public function destroy(BerkasPrr $berkasPrr)
    {
        foreach ([...$this->gambarFields, 'pdf_scan'] as $field) {

            if ($berkasPrr->$field) {
                Storage::disk('public')->delete($berkasPrr->$field);
            }
        }

        $berkasPrr->delete();

        return back()->with('success', 'Berkas PRR berhasil dihapus.');
    }

    /**
     * Validasi input.
     */
    private function validateBerkas(Request $request, $ignoreId = null): array
    {
        return $request->validate([
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
        ]);
    }

    /**
     * Hitung status upload berdasarkan jumlah gambar.
     */
    private function hitungStatusUlp($berkas): string
    {
        if ($berkas->isEmpty()) {
            return 'none';
        }

        $lengkap = 0;
        $belumLengkap = 0;

        foreach ($berkas as $item) {

            $terisi = collect($this->gambarFields)
                ->filter(fn($field) => !empty($item->$field))
                ->count();

            if ($terisi == count($this->gambarFields)) {
                $lengkap++;
            } elseif ($terisi > 0) {
                $belumLengkap++;
            }
        }

        if ($lengkap == $berkas->count()) {
            return 'complete';
        }

        if ($lengkap > 0 || $belumLengkap > 0) {
            return 'partial';
        }

        return 'none';
    }
}