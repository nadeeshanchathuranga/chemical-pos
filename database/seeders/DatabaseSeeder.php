<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        if (!\App\Models\User::where('email', 'admin@admin.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'admin',
                'email' => 'admin@admin.com',
                'role_type' => 'Admin',
                'password' => Hash::make('admin@chemicalpos2026'),
            ]);
        }

        if (!\App\Models\User::where('email', 'manager@manager.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'manager',
                'email' => 'manager@manager.com',
                'role_type' => 'Manager',
                'password' => Hash::make('manager@chemicalpos2026'),
            ]);
        }





        if (!\App\Models\User::where('email', 't1@cashier.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'cashier1',
                'email' => 't1@cashier.com',
                'role_type' => 'Cashier',
                'password' => Hash::make('cashier1@chemicalpos2026'),
            ]);
        }

        if (!\App\Models\User::where('email', 't2@cashier.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'cashier2',
                'email' => 't2@cashier.com',
                'role_type' => 'Cashier',
                'password' => Hash::make('cashier2@chemicalpos2026'),
            ]);
        }



    }
}
