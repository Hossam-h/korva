<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'المدير الرئيسي',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'مدير النظام',
                'email' => 'admin2@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'المشرف العام',
                'email' => 'supervisor@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($admins as $admin) {
            Admin::create($admin);
        }
    }
}
