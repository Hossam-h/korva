<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $players = [
            [
                'first_name' => 'أحمد',
                'last_name' => 'محمد',
                'email' => 'player1@example.com',
                'phone' => '0501234567',
                'gender' => 'male',
                'type' => 'player',
                'address' => 'الرياض، حي النرجس',
                'birth_date' => '2010-05-15',
                'weight' => 45.5,
                'has_health_issues' => false,
                'health_issues' => null,
                'password' => Hash::make('password'),
            ],
            [
                'first_name' => 'فاطمة',
                'last_name' => 'علي',
                'email' => 'player2@example.com',
                'phone' => '0501234568',
                'gender' => 'female',
                'type' => 'player',
                'address' => 'جدة، حي الزهراء',
                'birth_date' => '2012-08-20',
                'weight' => 38.2,
                'has_health_issues' => false,
                'health_issues' => null,
                'password' => Hash::make('password'),
            ],
            [
                'first_name' => 'خالد',
                'last_name' => 'سعيد',
                'email' => 'parent1@example.com',
                'phone' => '0501234569',
                'gender' => 'male',
                'type' => 'parent',
                'address' => 'الدمام، حي الفيصلية',
                'birth_date' => null,
                'weight' => null,
                'has_health_issues' => false,
                'health_issues' => null,
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($players as $player) {
            Player::create($player);
        }
    }
}
