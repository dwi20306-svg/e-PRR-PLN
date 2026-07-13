<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ================= ADMIN =================

        User::firstOrCreate(
            ['username' => 'admin_up3'],
            [
                'name'     => 'Admin UP3 Banda Aceh',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        // ================= PETUGAS ULP =================

        $ulps = [

            ['ulp_syiah_kuala',  'ULP Syiah Kuala',  'syiahkuala',   '11115'],
            ['ulp_jantho',       'ULP Jantho',       'jantho',       '11113'],
            ['ulp_sabang',       'ULP Sabang',       'sabang',       '11114'],
            ['ulp_merduati',     'ULP Merduati',     'merduati',     '11110'],
            ['ulp_lambaro',      'ULP Lambaro',      'lambaro',      '11112'],
            ['ulp_keudeu_bieng', 'ULP Keudeu Bieng', 'keudeubieng',  '11111'],

        ];

        foreach ($ulps as [$key, $name, $user, $password]) {

            User::firstOrCreate(

                ['username' => 'petugas_'.$user],

                [
                    'name'      => 'Petugas '.$name,
                    'password'  => Hash::make($password),
                    'role'      => 'ulp',
                    'ulp_name'  => $key,
                ]

            );

        }
    }
}
