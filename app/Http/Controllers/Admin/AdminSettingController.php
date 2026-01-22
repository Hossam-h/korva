<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminSetting;
class AdminSettingController extends Controller
{
   
       
        public function index()
        {
            try {
                $settings = AdminSetting::orderBy('group')
                    ->orderBy('key')
                    ->get()
                    ->groupBy('group')
                    ->map(function ($items, $group) {
                        return [
                            'group' => $group,
                            'settings' => $items->map(function ($setting) {
                                return [
                                    'key' => $setting->key,
                                    'value' => $setting->getCastedValue(),
                                    'type' => $setting->type,
                                    'is_public' => $setting->is_public,
                                ];
                            })->values()
                        ];
                    })->values();
    
                return response()->json([
                    'success' => true,
                    'data' => $settings
                ], 200);
    
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء جلب الإعدادات',
                    'error' => $e->getMessage()
                ], 500);
            }
        
    }
}
