<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_prr', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_unit');
            $table->string('id_pelanggan')->unique();
            $table->string('nama_pelanggan');
            $table->string('tarif');
            $table->integer('daya'); // dalam VA
            $table->integer('lembar');
            $table->decimal('tagihan', 15, 2);
            $table->date('tanggal_periksa')->nullable();
            $table->enum('kondisi_lapangan', ['bongkar rampung', 'rata dengan tanah', 'sr seri', 'sr/ok belum rampung'])->nullable();

            // Dokumen gambar
            $table->string('gambar_tul_vi01')->nullable();  // Surat Pemutusan
            $table->string('gambar_tul_vi03')->nullable();  // Pembongkaran
            $table->string('gambar_spk')->nullable();   // SPK
            $table->string('gambar_berita_acara')->nullable();
            $table->string('gambar_pdp')->nullable();
            $table->string('gambar_tug10')->nullable();
            $table->string('gambar_invoice')->nullable();
            $table->string('gambar_rumah')->nullable(); // Gambar rumah & bekas bongkar meteran

            // Titik koordinat
            $table->decimal('koordinat_x', 15, 8)->nullable();
            $table->decimal('koordinat_y', 15, 8)->nullable();

            // PDF hasil scan
            $table->string('pdf_scan')->nullable();

            // ULP yang menginput
            $table->enum('ulp', [
                'ulp_syiah_kuala',
                'ulp_jantho',
                'ulp_sabang',
                'ulp_merduati',
                'ulp_lambaro',
                'ulp_keudeu_bieng'
            ]);

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_prr');
    }
};
