<?php

namespace App\Http\Controllers\AdminUP3;

use App\Models\BerkasPrr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ImportErrorExport;
use App\Imports\BerkasPrrImport;
use App\Exports\BerkasPrrExport;

class BerkasPrrController extends Controller
{

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

    private array $ulpList = [
        'ulp_syiah_kuala'  => 'ULP Syiah Kuala',
        'ulp_jantho'       => 'ULP Jantho',
        'ulp_sabang'       => 'ULP Sabang',
        'ulp_merduati'     => 'ULP Merduati',
        'ulp_lambaro'      => 'ULP Lambaro',
        'ulp_keudeu_bieng' => 'ULP Keudeu Bieng',
    ];

    /**
     * Menampilkan daftar berkas detail (Mendukung filter klik sidebar & bulatan warna)
     */
    public function index(Request $request)
    {
        $ulp = $request->get('ulp');
        $search = $request->get('search');

        $query = BerkasPrr::query();

        if ($ulp) {
            $query->where('ulp', $ulp);
            $judul = $this->ulpList[$ulp];
        } else {
            $judul = 'Semua ULP';
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id_pelanggan', 'like', "%{$search}%")
                ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                ->orWhere('nomor_unit', 'like', "%{$search}%");
            });
        }

        $totalTagihan = (clone $query)->sum('tagihan');

        $berkas = $query->latest()->paginate(20)->withQueryString();

        $statusUlp = [];

        foreach ($this->ulpList as $key => $label) {
            $statusUlp[$key] = $this->hitungStatusUlp(
                BerkasPrr::where('ulp', $key)->get()
            );
        }

        $currentStatus = $ulp
            ? ($statusUlp[$ulp] ?? 'none')
            : 'all';

        return view('admin.berkas', [
            'berkas'        => $berkas,
            'ulp'           => $ulp,
            'judul'         => $judul,
            'totalTagihan'  => $totalTagihan,
            'statusUlp'     => $statusUlp,
            'currentStatus' => $currentStatus,
            'canImport'     => !$ulp,
            'canManage'     => (bool) $ulp,
            'ulpList'       => $this->ulpList,
        ]);
    }
    /**
     * Fitur Import Excel Berkas e-PRR (Dari AdminController lama)
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {

            $import = new BerkasPrrImport();

            Excel::import(
                $import,
                $request->file('file')
            );

            $errors = $import->getErrors();

            $berhasil = $import->getSuccess();

            $gagal = count($errors);

            /*
            |--------------------------------------------------------------------------
            | Kalau ada data gagal
            |--------------------------------------------------------------------------
            */

            if ($gagal > 0) {

                $namaFile = 'import_error_'.date('Ymd_His').'.xlsx';

                Excel::store(
                    new ImportErrorExport($errors),
                    'import_errors/'.$namaFile
                );

                return back()->with([

                    'warning' => 'Import selesai dengan beberapa kesalahan.',

                    'success_count' => $berhasil,

                    'failed_count' => $gagal,

                    'download_error' => $namaFile,

                ]);

            }

            return back()->with(

                'success',

                "Import berhasil. {$berhasil} data berhasil ditambahkan."

            );

        } catch (\Throwable $e) {

            return back()->with(

                'error',

                'Import gagal : '.$e->getMessage()

            );

        }
    }

    public function downloadImportError($file)
    {
        $path = storage_path('app/import_errors/' . $file);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request)
    {
        $ulp = $request->ulp;

        $nama = $ulp
            ? "berkas_{$ulp}.xlsx"
            : "semua_berkas.xlsx";

        return Excel::download(
            new BerkasPrrExport($ulp),
            $nama
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateBerkas($request);

        $data['ulp'] = $this->getUlpFromNomorUnit(
            $request->nomor_unit
        );

        $data['user_id'] = auth()->id();

        $this->uploadFiles(
            $request,
            $data,
            $data['ulp']
        );

        $this->updateStatus($data);

        BerkasPrr::create($data);

        return back()->with(
            'success',
            'Berkas berhasil ditambahkan.'
        );
    }

    public function update(Request $request, BerkasPrr $berkasPrr)
    {
        $data = $this->validateBerkas(
            $request,
            $berkasPrr->id
        );

        $data['ulp'] = $this->getUlpFromNomorUnit(
            $request->nomor_unit
        );

        $this->uploadFiles(
            $request,
            $data,
            $data['ulp'],
            $berkasPrr
        );

        $this->updateStatus(
            $data,
            $berkasPrr
        );

        $berkasPrr->update($data);

        return back()->with(
            'success',
            'Berkas berhasil diperbarui.'
        );
    }

    public function destroy(BerkasPrr $berkasPrr)
    {
        foreach ([...$this->gambarFields, 'pdf_scan'] as $field) {

            if ($berkasPrr->$field) {
                Storage::disk('public')->delete($berkasPrr->$field);
            }

        }

        $berkasPrr->delete();

        return back()->with(
            'success',
            'Berkas berhasil dihapus.'
        );
    }

    /**
     * Menghitung status upload ULP untuk warna bulatan di sidebar (none/partial/complete)
     */
    private function hitungStatusUlp($berkas): string
    {
        if ($berkas->isEmpty()) {
            return 'none';
        }

        $semuaComplete = $berkas->every(fn($b) => $b->status_upload === 'complete');
        $adaUpload = $berkas->contains(fn($b) => $b->status_upload !== 'none');

        return $semuaComplete ? 'complete' : ($adaUpload ? 'partial' : 'none');
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
                'tanggal_periksa'  => 'nullable|date',

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

    private function uploadFiles(Request $request, array &$data, string $ulp, ?BerkasPrr $old = null)
    {
        foreach ($this->gambarFields as $field) {

            if (!$request->hasFile($field)) {
                continue;
            }

            if ($old && $old->$field) {
                Storage::disk('public')->delete($old->$field);
            }

            $data[$field] = $request->file($field)
                ->store("berkas/$ulp/$field", 'public');
        }

        if ($request->hasFile('pdf_scan')) {

            if ($old && $old->pdf_scan) {
                Storage::disk('public')->delete($old->pdf_scan);
            }

            $data['pdf_scan'] = $request->file('pdf_scan')
                ->store("berkas/$ulp/pdf", 'public');
        }
    }

    private function updateStatus(array &$data, ?BerkasPrr $old = null)
    {
        $terisi = 0;

        foreach ($this->gambarFields as $field) {

            if (
                !empty($data[$field]) ||
                ($old && !empty($old->$field))
            ) {
                $terisi++;
            }

        }

        if (
            !empty($data['pdf_scan']) ||
            ($old && !empty($old->pdf_scan))
        ) {
            $terisi++;
        }

        $total = count($this->gambarFields) + 1;

        if ($terisi == 0) {

            $data['status_berkas'] = 'belum upload';

        } elseif ($terisi < $total) {

            $data['status_berkas'] = 'belum lengkap';

        } else {

            $data['status_berkas'] = 'lengkap';

        }
    }

    private function getUlpFromNomorUnit($nomorUnit): string
    {
        $nomorUnit = (int) $nomorUnit;

        return match ($nomorUnit) {

            11110 => 'ulp_merduati',
            11111 => 'ulp_keudeu_bieng',
            11112 => 'ulp_lambaro',
            11113 => 'ulp_jantho',
            11114 => 'ulp_sabang',
            11115 => 'ulp_syiah_kuala',

            default => throw new \Exception(
                "Nomor unit {$nomorUnit} tidak dikenali."
            ),
        };
    }
}
