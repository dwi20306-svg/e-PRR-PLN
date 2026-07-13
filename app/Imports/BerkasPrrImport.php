<?php

namespace App\Imports;

use App\Models\BerkasPrr;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BerkasPrrImport implements ToModel, WithHeadingRow
{
    use RemembersRowNumber;

    private array $errors = [];

    /**
     * Menyimpan kombinasi data yang sudah muncul di file Excel
     * agar baris yang benar-benar sama tidak diimport dua kali.
     */
    private array $dataDalamFile = [];
    private int $success = 0;

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccess(): int
    {
        return $this->success;
    }

    private function gagal(array $row, string $pesan)
    {
        $this->errors[] = [

            'baris' => $this->getRowNumber(),

            'nomor_unit' => $row['nomor_unit'] ?? '',

            'id_pelanggan' => $row['id_pelanggan'] ?? '',

            'nama_pelanggan' => $row['nama_pelanggan'] ?? '',

            'tarif' => $row['tarif'] ?? '',

            'daya' => $row['daya'] ?? '',

            'lembar' => $row['lembar'] ?? '',

            'tagihan' => $row['tagihan'] ?? '',

            'tanggal_periksa' => $row['tanggal_periksa'] ?? '',

            'koordinat_x' => $row['koordinat_x'] ?? '',

            'koordinat_y' => $row['koordinat_y'] ?? '',

            'kondisi_lapangan' => $row['kondisi_lapangan'] ?? '',

            'alasan' => $pesan,

        ];

        return null;
    }

    private function parseTanggal($tanggal)
    {
        if (empty($tanggal)) {
            return null;
        }

        // Jika berupa angka serial Excel
        if (is_numeric($tanggal)) {
            try {
                return Date::excelToDateTimeObject($tanggal)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                return false;
            }
        }

        // Jika berupa teks
        try {
            return date('Y-m-d', strtotime($tanggal));
        } catch (\Exception $e) {
            return false;
        }
    }

    public function model(array $row)
    {
        $idPelanggan = trim((string)($row['id_pelanggan'] ?? ''));

        $tanggalPeriksa = $this->parseTanggal($row['tanggal_periksa']);

        // Validasi Nomor Unit
        if (!in_array((int)($row['nomor_unit'] ?? 0), [11110,11111,11112,11113,11114,11115])) {

            return $this->gagal(
                $row,"Nomor Unit '{$row['nomor_unit']}' tidak dikenali."
            );

        }

        // Validasi ID Pelanggan
        if ($idPelanggan == '') {

            return $this->gagal(
                $row,"ID Pelanggan tidak boleh kosong."
            );

        }

        // Kombinasi data untuk mendeteksi duplikat persis di dalam file Excel
        $signature = md5(json_encode([
            (int) $row['nomor_unit'],
            $idPelanggan,
            strtoupper(trim($row['nama_pelanggan'] ?? '')),
            strtoupper(trim($row['tarif'] ?? '')),
            (int) ($row['daya'] ?? 0),
            (int) ($row['lembar'] ?? 0),
            (float) ($row['tagihan'] ?? 0),
            $tanggalPeriksa,
            strtoupper(trim($row['kondisi_lapangan'] ?? '')),
            $row['koordinat_x'] ?? null,
            $row['koordinat_y'] ?? null,
        ]));

        if (isset($this->dataDalamFile[$signature])) {

            return $this->gagal(
                $row,"Baris ini merupakan duplikat dari data sebelumnya di file Excel."
            );

        }

        $this->dataDalamFile[$signature] = true;

        // Cari data dengan ID yang sama
        $dataLama = BerkasPrr::where('id_pelanggan', $idPelanggan)->first();

        if ($dataLama) {

            // Nama berbeda → tolak
            if (
                strtoupper(trim($dataLama->nama_pelanggan))
                !=
                strtoupper(trim($row['nama_pelanggan']))
            ) {

                return $this->gagal(
                    $row,"ID Pelanggan {$idPelanggan} sudah terdaftar atas nama '{$dataLama->nama_pelanggan}', tetapi pada file tertulis '{$row['nama_pelanggan']}'."
                );

            }

            // Cek apakah semua data sama (duplikat)
            $duplikat = BerkasPrr::where([
                'nomor_unit'       => $row['nomor_unit'],
                'id_pelanggan'     => $idPelanggan,
                'nama_pelanggan'   => $row['nama_pelanggan'],
                'tarif'            => $row['tarif'],
                'daya'             => $row['daya'],
                'lembar'           => $row['lembar'],
                'tagihan'          => $row['tagihan'],
                'tanggal_periksa'  => $tanggalPeriksa,
                'kondisi_lapangan' => $row['kondisi_lapangan'],
                'koordinat_x'      => empty($row['koordinat_x']) ? null : $row['koordinat_x'],
                'koordinat_y'      => empty($row['koordinat_y']) ? null : $row['koordinat_y'],
            ])->exists();

            if ($duplikat) {

                return $this->gagal(
                    $row,"Data identik dengan id pelanggan {$idPelanggan} sudah pernah diimport."
                );

            }

        }

        // Validasi Nama Pelanggan
        if (empty($row['nama_pelanggan'])) {

            return $this->gagal(
                $row,"Nama Pelanggan tidak boleh kosong."
            );

        }

        // Validasi Tarif
        if (empty($row['tarif'])) {

            return $this->gagal(
                $row,"Tarif tidak boleh kosong."
            );

        }

        // Validasi Daya
        if (!is_numeric($row['daya'])) {

            return $this->gagal(
                $row,"Daya pada ID {$idPelanggan} harus berupa angka."
            );

        }

        // Validasi Lembar
        if (!is_numeric($row['lembar'])) {

            return $this->gagal(
                $row,"Jumlah lembar pada ID {$idPelanggan} harus berupa angka."
            );

        }
        // Validasi Tagihan
        if (!is_numeric($row['tagihan'])) {

            return $this->gagal(
                $row,"Tagihan pada ID {$idPelanggan} harus berupa angka."
            );

        }

        // Validasi Tanggal Periksa

        if (
            !empty($row['tanggal_periksa']) &&
            $tanggalPeriksa === false
        ) {
            return $this->gagal(
                $row,"Tanggal Periksa pada ID {$idPelanggan} tidak valid."
            );
        }

        // Validasi Koordinat X
        if (!empty($row['koordinat_x'])) {

            if (!is_numeric($row['koordinat_x'])) {
                return $this->gagal(
                    $row,"Koordinat X pada ID Pelanggan {$idPelanggan} harus berupa angka."
                );
            }

            if ($row['koordinat_x'] < -180 || $row['koordinat_x'] > 180) {
                return $this->gagal(
                    $row,"Koordinat X pada ID Pelanggan {$idPelanggan} berada di luar batas yang diperbolehkan."
                );
            }
        }

        // Validasi Koordinat Y
        if (!empty($row['koordinat_y'])) {

            if (!is_numeric($row['koordinat_y'])) {
                return $this->gagal(
                    $row,"Koordinat Y pada ID Pelanggan {$idPelanggan} harus berupa angka."
                );
            }

            if ($row['koordinat_y'] < -90 || $row['koordinat_y'] > 90) {
                return $this->gagal(
                    $row,"Koordinat Y pada ID Pelanggan {$idPelanggan} berada di luar batas yang diperbolehkan."
                );
            }
        }

        // Validasi Kondisi Lapangan
        if (
            !empty($row['kondisi_lapangan']) &&
            !in_array(
                strtolower(trim($row['kondisi_lapangan'])),
                [
                    'bongkar rampung',
                    'rata dengan tanah',
                    'sr seri',
                    'sr/ok belum rampung',
                ]
            )
        ) {

            return $this->gagal(
                $row,"Kondisi Lapangan pada ID {$idPelanggan} tidak valid."
            );

        }

        try {

    BerkasPrr::create([

        'nomor_unit'       => $row['nomor_unit'],

        'ulp'              => $this->getUlpFromNomorUnit($row['nomor_unit']),

        'id_pelanggan'     => $idPelanggan,

        'nama_pelanggan'   => $row['nama_pelanggan'],

        'tarif'            => $row['tarif'],

        'daya'             => $row['daya'],

        'lembar'           => $row['lembar'],

        'tagihan'          => $row['tagihan'],

        'tanggal_periksa'  => $tanggalPeriksa,

        'koordinat_x'      => empty($row['koordinat_x']) ? null : $row['koordinat_x'],

        'koordinat_y'      => empty($row['koordinat_y']) ? null : $row['koordinat_y'],

        'kondisi_lapangan' => $row['kondisi_lapangan'],

        'status_berkas'    => 'belum upload',

        'user_id'          => Auth::id(),

    ]);

    $this->success++;

    } catch (\Throwable $e) {

        return $this->gagal(
            $row,
            $e->getMessage()
        );

    }

    return null;
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
