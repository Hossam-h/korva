<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AdminSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'اسم الموقع', 'type' => 'text', 'group' => 'general'],
            ['key' => 'admin_email', 'value' => 'user@email.com', 'type' => 'email', 'group' => 'general'],
            
            // Notifications
            ['key' => 'email_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'notifications'],
            ['key' => 'sms_notifications', 'value' => '0', 'type' => 'boolean', 'group' => 'notifications'],
            
            // Language & Timezone
            ['key' => 'default_language', 'value' => 'ar', 'type' => 'select', 'group' => 'localization'],
            ['key' => 'timezone', 'value' => 'GMT+3', 'type' => 'timezone', 'group' => 'localization'],
            
            // Features
            ['key' => 'enable_new_registrations', 'value' => '1', 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'enable_email_verification', 'value' => '0', 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'enable_reports', 'value' => '1', 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'enable_statistics', 'value' => '1', 'type' => 'boolean', 'group' => 'features'],
        ];

        foreach ($settings as $setting) {
            DB::table('admin_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
