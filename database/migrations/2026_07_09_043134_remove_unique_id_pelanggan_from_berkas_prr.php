<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE berkas_prr
            DROP INDEX berkas_prr_id_pelanggan_unique
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE berkas_prr
            ADD UNIQUE (id_pelanggan)
        ");
    }
};
