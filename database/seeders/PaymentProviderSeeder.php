<?php

namespace Database\Seeders;

use App\Models\PaymentProvider;
use Illuminate\Database\Seeder;

class PaymentProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'name'         => 'visa',
                'display_name' => 'Visa',
                'logo'         => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'mastercard',
                'display_name' => 'Mastercard',
                'logo'         => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'mada',
                'display_name' => 'Mada',
                'logo'         => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'apple_pay',
                'display_name' => 'Apple Pay',
                'logo'         => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'stc_pay',
                'display_name' => 'STC Pay',
                'logo'         => null,
                'is_active'    => true,
            ],
        ];

        foreach ($providers as $provider) {
            PaymentProvider::firstOrCreate(
                ['name' => $provider['name']],
                $provider
            );
        }
    }
}
