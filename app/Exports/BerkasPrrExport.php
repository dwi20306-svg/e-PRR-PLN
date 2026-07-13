<?php

namespace App\Exports;

use App\Models\BerkasPrr;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BerkasPrrExport implements FromCollection, WithHeadings
{
    protected $ulp;

    public function __construct($ulp = null)
    {
        $this->ulp = $ulp;
    }

    public function collection()
    {
        $query = BerkasPrr::select([
            'nomor_unit',
            'id_pelanggan',
            'nama_pelanggan',
            'tarif',
            'daya',
            'lembar',
            'tagihan',
            'tanggal_periksa',
            'koordinat_x',
            'koordinat_y',
            'kondisi_lapangan',
        ]);

        if ($this->ulp) {
            $query->where('ulp', $this->ulp);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Nomor Unit',
            'ID Pelanggan',
            'Nama Pelanggan',
            'Tarif',
            'Daya',
            'Lembar',
            'Tagihan',
            'Tanggal Periksa',
            'Koordinat X',
            'Koordinat Y',
            'Kondisi Lapangan',
        ];
    }
}
