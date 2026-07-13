<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'ulp']);
            $table->enum('ulp_name', [
                'ulp_syiah_kuala',
                'ulp_jantho',
                'ulp_sabang',
                'ulp_merduati',
                'ulp_lambaro',
                'ulp_keudeu_bieng'
            ])->nullable(); // hanya untuk role ulp
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
