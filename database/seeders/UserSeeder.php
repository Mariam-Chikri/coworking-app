<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin CoWork',
            'email' => 'admin@coworking.fr',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'locale' => 'fr',
            'email_verified_at' => now(),
        ]);

        // Utilisateurs de test
        $users = [
            ['name' => 'Marie Dupont', 'email' => 'marie@example.fr', 'entreprise' => 'Design Studio'],
            ['name' => 'Pierre Martin', 'email' => 'pierre@example.fr', 'entreprise' => 'Tech Corp'],
            ['name' => 'Sophie Bernard', 'email' => 'sophie@example.fr', 'entreprise' => 'Freelance'],
            ['name' => 'John Smith', 'email' => 'john@example.com', 'entreprise' => 'UK Consulting', 'locale' => 'en'],
        ];

        foreach ($users as $data) {
            User::create(array_merge([
                'password' => Hash::make('password'),
                'locale' => 'fr',
                'email_verified_at' => now(),
            ], $data));
        }
    }
}
