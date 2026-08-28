<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password']
        );

        foreach (range(1, 4) as $number) {
            User::updateOrCreate(
                ['email' => "admin{$number}@impulso.local"],
                ['name' => "admin{$number}", 'password' => "admin{$number}", 'role' => 'superadmin', 'is_entrepreneur' => true]
            );
        }
    }
}
