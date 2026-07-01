<?php

namespace App\Http\Controllers;

use App\Models\BerkasPrr;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BerkasPrrImport;

class AdminController extends Controller
{
    private array $ulpList = [
        'ulp_syiah_kuala'  => 'ULP Syiah Kuala',
        'ulp_jantho'       => 'ULP Jantho',
        'ulp_sabang'       => 'ULP Sabang',
        'ulp_merduati'     => 'ULP Merduati',
        'ulp_lambaro'      => 'ULP Lambaro',
        'ulp_keudeu_bieng' => 'ULP Keudeu Bieng',
    ];

    public function dashboard(Request $request)
    {
        $ulp = $request->get('ulp', array_key_first($this->ulpList));

        $berkas = BerkasPrr::where('ulp', $ulp)
            ->orderByDesc('created_at')
            ->get();

        $totalTagihan = $berkas->sum('tagihan');

        $statusUlp = [];

        foreach ($this->ulpList as $key => $label) {
            $statusUlp[$key] = $this->hitungStatusUlp(
                BerkasPrr::where('ulp', $key)->get()
            );
        }

        $currentStatus = $statusUlp[$ulp] ?? 'none';

        return view('admin.dashboard', compact(
            'berkas',
            'ulp',
            'totalTagihan',
            'statusUlp'
        ))->with('ulpList', $this->ulpList)
          ->with('currentStatus', $currentStatus);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
            'ulp'  => 'required|in:' . implode(',', array_keys($this->ulpList)),
        ]);

        try {

            Excel::import(
                new BerkasPrrImport($request->ulp),
                $request->file('file')
            );

            return back()->with(
                'success',
                'Data berhasil diimport dari Excel.'
            );

        } catch (\Exception $e) {

            return back()->with(
                'error',
                'Gagal import: ' . $e->getMessage()
            );
        }
    }

    /**
     * Menghitung status upload suatu ULP.
     */
    private function hitungStatusUlp($berkas): string
    {
        if ($berkas->isEmpty()) {
            return 'none';
        }

        $semuaComplete = $berkas->every(
            fn($b) => $b->status_upload === 'complete'
        );

        $adaUpload = $berkas->contains(
            fn($b) => $b->status_upload !== 'none'
        );

        return $semuaComplete
            ? 'complete'
            : ($adaUpload ? 'partial' : 'none');
    }
}