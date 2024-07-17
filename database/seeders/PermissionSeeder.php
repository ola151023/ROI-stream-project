<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create([
            'name'=>'give_permission',
            'name'=>'get-accumulated-profit',
            'name'=>'add_user',
            'name'=>'approve_account',
            'name'=>'withdraw_profit',
            'name'=>'grant_withdrawal_permission',
            'name'=>'close_investor_cycle',
        ]);
    }
}
