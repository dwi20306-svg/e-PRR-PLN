<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerkasPrr extends Model
{
    protected $table = 'berkas_prr';

    protected $fillable = [
        'nomor_unit', 
        'id_pelanggan', 
        'nama_pelanggan', 
        'tarif',
        'daya', 
        'lembar', 
        'tagihan', 
        'tanggal_periksa',
        'gambar_tul_vi01', 
        'gambar_tul_vi03', 
        'gambar_spk',
        'gambar_berita_acara', 
        'gambar_pdp', 
        'gambar_tug10',
        'gambar_invoice', 
        'gambar_rumah',
        'koordinat_x', 
        'koordinat_y',
        'pdf_scan', 'ulp', 
        'user_id', 
        'kondisi_lapangan', 
        'status_berkas'
    ];

    protected $casts = [
        'tanggal_periksa' => 'date',
        'tagihan'         => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Daftar field gambar untuk cek status upload
   public static function fieldGambar(): array
    {
        return [
            'gambar_tul_vi01'     => 'TUL VI-01',
            'gambar_tul_vi03'     => 'TUL VI-03',
            'gambar_spk'          => 'SPK',
            'gambar_berita_acara' => 'Berita Acara',
            'gambar_pdp'          => 'PDP',
            'gambar_tug10'        => 'TUG 10',
            'gambar_invoice'      => 'Invoice',
            'gambar_rumah'        => 'Gambar Rumah & Bekas Bongkar',
            'pdf_scan'            => 'PDF Scan Seluruh Dokumen',
        ];
    }
    // Hitung status upload: none | partial | complete
    public function getStatusUploadAttribute(): string
    {
        $fields = array_keys(self::fieldGambar());
        $terisi = collect($fields)->filter(fn($f) => !empty($this->$f))->count();

        if ($terisi === 0) return 'none';
        if ($terisi < count($fields)) return 'partial';
        return 'complete';
    }

    public function getUlpLabelAttribute(): string
    {
        return match($this->ulp) {
            'ulp_syiah_kuala' => 'ULP Syiah Kuala',
            'ulp_jantho'      => 'ULP Jantho',
            'ulp_sabang'      => 'ULP Sabang',
            'ulp_merduati'    => 'ULP Merduati',
            'ulp_lambaro'     => 'ULP Lambaro',
            'ulp_keudeu_bieng' => 'ULP Keudeu Bieng',
            default           => '-',
        };
    }
}
