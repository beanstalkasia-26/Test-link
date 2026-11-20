<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_ADMIN_PASSWORD') ?: Str::password(20);
        $email = env('SEED_ADMIN_EMAIL', 'admin@namtech.test');

        $accounts = [[$email, 'Site Administrator', Role::SuperAdmin]];
        if (app()->environment('local', 'testing')) {
            $accounts[] = ['publisher@namtech.test', 'Test Publisher', Role::Publisher];
            $accounts[] = ['editor@namtech.test', 'Test Editor', Role::Editor];
        }

        foreach ($accounts as [$address, $name, $role]) {
            $user = User::query()->firstOrCreate(['email' => $address], [
                'name' => $name,
                'password' => $password,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->syncRoles([$role->value]);
        }

        if (! env('SEED_ADMIN_PASSWORD')) {
            $this->command?->warn("Super Admin {$email} created with a generated password: {$password}");
            $this->command?->warn('Store it securely and change it after first sign-in.');
        }
    }
}
