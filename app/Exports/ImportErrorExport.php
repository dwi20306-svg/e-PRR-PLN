<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ImportErrorExport implements FromArray, WithHeadings
{
    protected array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function headings(): array
    {
        return [
            'No',
            'Baris Excel',
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
            'Alasan Gagal'
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->errors as $i => $error) {

            $rows[] = [

                $i + 1,

                $error['baris'],

                $error['nomor_unit'],

                $error['id_pelanggan'],

                $error['nama_pelanggan'],

                $error['tarif'],

                $error['daya'],

                $error['lembar'],

                $error['tagihan'],

                $error['tanggal_periksa'],

                $error['koordinat_x'],

                $error['koordinat_y'],

                $error['kondisi_lapangan'],

                $error['alasan'],

            ];

        }

        return $rows;
    }
}
