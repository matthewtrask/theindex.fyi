<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@theindex.fyi'],
            [
                'name' => 'Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('changeme'),
                'remember_token' => Str::random(10),
            ]
        );

        $this->call(IndexSeeder::class);
    }
}
