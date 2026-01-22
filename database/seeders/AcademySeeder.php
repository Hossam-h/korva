<?php

namespace Database\Seeders;

use App\Models\Academy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academies = [
            [
                'name' => 'أكاديمية النجوم الرياضية',
                'email' => 'academy1@example.com',
                'phone' => '0112345678',
                'age_group' => '8-16',
                'country' => 'السعودية',
                'city' => 'الرياض',
                'address' => 'الرياض، حي العليا، شارع الملك فهد',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'أكاديمية الأبطال',
                'email' => 'academy2@example.com',
                'phone' => '0123456789',
                'age_group' => '10-18',
                'country' => 'السعودية',
                'city' => 'جدة',
                'address' => 'جدة، حي الزهراء، شارع التحلية',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'أكاديمية النجاح الرياضي',
                'email' => 'academy3@example.com',
                'phone' => '0134567890',
                'age_group' => '6-14',
                'country' => 'السعودية',
                'city' => 'الدمام',
                'address' => 'الدمام، حي الفيصلية، شارع الأمير سلطان',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($academies as $academy) {
            Academy::create($academy);
        }
    }
}
