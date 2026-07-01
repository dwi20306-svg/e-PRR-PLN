<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Menggunakan Raw SQL untuk mengubah kolom tanpa perlu doctrine/dbal
        DB::statement('ALTER TABLE berkas_prr MODIFY koordinat_x DECIMAL(15,8)');
        DB::statement('ALTER TABLE berkas_prr MODIFY koordinat_y DECIMAL(15,8)');
    }

    public function down(): void
    {
        // Mengembalikan ke tipe integer jika perlu
        DB::statement('ALTER TABLE berkas_prr MODIFY koordinat_x INT');
        DB::statement('ALTER TABLE berkas_prr MODIFY koordinat_y INT');
    }
};