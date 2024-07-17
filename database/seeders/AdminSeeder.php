<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'phone' => '123456789',
            'is_approved' => true,
            'is_active' => true,
            'account_number' => 'A000',
            'remember_token' => Str::random(60),
        ]);
        // Attach role to user
        $role = Role::where('name', 'Admin')->first();
        $user->roles()->attach($role);
        
    }
}
