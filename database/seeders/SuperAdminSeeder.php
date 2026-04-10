<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => config('app.super_admin_email', 'admin@sublicious.app')],
            [
                'name' => 'Super Admin',
                'email' => config('app.super_admin_email', 'admin@sublicious.app'),
                'password' => Hash::make(config('app.super_admin_password', 'password')),
                'role' => 'super_admin',
                'business_id' => null,
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $this->command->info('Super admin created: ' . config('app.super_admin_email', 'admin@sublicious.app'));
    }
}
