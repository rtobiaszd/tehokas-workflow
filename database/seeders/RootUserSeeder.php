<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RootUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'root@system.local'],
            [
                'name' => 'Root Admin',
                'password' => Hash::make('root123'),
                'role' => 'root',
                'tenant_id' => null,
            ]
        );
    }
}
