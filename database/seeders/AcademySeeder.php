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
                'latitude' => 24.7136,
                'longitude' => 46.6753,
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
                'latitude' => 21.5433,
                'longitude' => 39.1728,
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
                'latitude' => 26.4207,
                'longitude' => 50.0888,
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($academies as $academy) {
            Academy::create($academy);
        }
    }
}
