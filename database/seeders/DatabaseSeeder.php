<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin UP3
        User::create([
            'name'     => 'Admin UP3 Banda Aceh',
            'username' => 'admin_up3',
            'email'    => 'admin@up3bandaaceh.pln.co.id',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        // Akun ULP
        $ulps = [
            ['ulp_syiah_kuala', 'ULP Syiah Kuala', 'syiahkuala'],
            ['ulp_jantho',      'ULP Jantho',      'jantho'],
            ['ulp_sabang',      'ULP Sabang',      'sabang'],
            ['ulp_merduati',    'ULP Merduati',    'merduati'],
            ['ulp_lambaro',     'ULP Lambaro',     'lambaro'],
            ['ulp_keudeu_bieng', 'ULP Keudeu Bieng', 'keudeubieng'],
        ];

        foreach ($ulps as [$key, $name, $user]) {
            User::create([
                'name'     => 'Petugas ' . $name,
                'username' => 'petugas_' . $user,
                'email'    => $user . '@up3bandaaceh.pln.co.id',
                'password' => Hash::make('ulp123'),
                'role'     => 'ulp',
                'ulp_name' => $key,
            ]);
        }
    }
}
