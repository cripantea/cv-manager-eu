<?php

namespace Database\Seeders;

use App\Models\Cv;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name'         => 'Irene Sampieri',
            'email'        => 'irene@unisystems.eu',
            'password'     => 'admin1234',
            'role'         => 'admin',
            'is_suspended' => false,
        ]);

        Cv::create([
            'user_id'    => $admin->id,
            'first_name' => '',
            'last_name'  => '',
            'status'     => 'draft',
        ]);

        $candidate = User::create([
            'name'         => 'Mario Rossi',
            'email'        => 'mario@test.eu',
            'password'     => 'candidate1234',
            'role'         => 'candidate',
            'is_suspended' => false,
        ]);

        Cv::create([
            'user_id'    => $candidate->id,
            'first_name' => '',
            'last_name'  => '',
            'status'     => 'draft',
        ]);
    }
}
