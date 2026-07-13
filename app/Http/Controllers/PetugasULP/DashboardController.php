<?php

namespace App\Http\Controllers\PetugasULP;

use App\Http\Controllers\Controller;
use App\Models\BerkasPrr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private array $ulpData = [
        'ulp_merduati'     => ['label' => 'ULP Merduati', 'nomor_unit' => '11110'],
        'ulp_keudeu_bieng' => ['label' => 'ULP Keudeu Bieng', 'nomor_unit' => '11111'],
        'ulp_lambaro'      => ['label' => 'ULP Lambaro', 'nomor_unit' => '11112'],
        'ulp_jantho'       => ['label' => 'ULP Jantho', 'nomor_unit' => '11113'],
        'ulp_sabang'       => ['label' => 'ULP Sabang', 'nomor_unit' => '11114'],
        'ulp_syiah_kuala'  => ['label' => 'ULP Syiah Kuala', 'nomor_unit' => '11115'],
    ];

    private array $gambarFields = [
        'gambar_tul_vi01', 'gambar_tul_vi03', 'gambar_spk', 'gambar_berita_acara',
        'gambar_pdp', 'gambar_tug10', 'gambar_invoice', 'gambar_rumah',
    ];

    public function index()
    {
        $user = Auth::user();
        $userUlp = $user->ulp_name; // Mengambil nama ULP petugas yang login

        // Keamanan ketat: Isolasi array agar hanya memproses ULP miliknya saja
        $filteredUlp = array_filter(
            $this->ulpData,
            fn($key) => $key === $userUlp,
            ARRAY_FILTER_USE_KEY
        );

        $rekapData = [];
        $grandTotal = 0; $grandBerkas = 0; $grandLengkap = 0;
        $grandBelumLengkap = 0; $grandBelumUpload = 0;

        foreach ($filteredUlp as $key => $info) {
            $berkas = BerkasPrr::where('ulp', $key)->get();

            $jumlahBerkas = $berkas->count();
            $totalTagihan = $berkas->sum('tagihan');
            $status = $this->hitungStatusBerkas($berkas);

            $rekapData[$key] = [
                'label'           => $info['label'],
                'nomor_unit'      => $info['nomor_unit'],
                'jumlah_berkas'   => $jumlahBerkas,
                'total_tagihan'   => $totalTagihan,
                'lengkap'         => $status['lengkap'],
                'belum_lengkap'   => $status['belum_lengkap'],
                'belum_upload'    => $status['belum_upload'],
            ];

            $grandBerkas += $jumlahBerkas;
            $grandTotal += $totalTagihan;
            $grandLengkap += $status['lengkap'];
            $grandBelumLengkap += $status['belum_lengkap'];
            $grandBelumUpload += $status['belum_upload'];
        }

        // Mengarah ke view dashboard khusus petugas
        return view('ulp.dashboard', compact(
            'userUlp', 'rekapData', 'grandTotal', 'grandBerkas',
            'grandLengkap', 'grandBelumLengkap', 'grandBelumUpload'
        ));
    }

    private function hitungStatusBerkas($berkas): array
    {
        $lengkap = 0; $belumLengkap = 0; $belumUpload = 0;

        foreach ($berkas as $item) {
            $terisi = collect($this->gambarFields)->filter(fn($field) => !empty($item->$field))->count();
            if ($terisi === 0) { $belumUpload++; }
            elseif ($terisi < count($this->gambarFields)) { $belumLengkap++; }
            else { $lengkap++; }
        }

        return ['lengkap' => $lengkap, 'belum_lengkap' => $belumLengkap, 'belum_upload' => $belumUpload];
    }
}
