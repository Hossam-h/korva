<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\UpdateAcademyAcademicSettingRequest;
use App\Models\AcademyAcademicSetting;
use Illuminate\Support\Facades\Auth;

class AcademyAcademicSettingController extends BaseController
{
    /**
     * Update the academic setting for the authenticated academy.
     * Uses updateOrCreate so it works even if no record exists yet.
     */
    public function update(UpdateAcademyAcademicSettingRequest $request)
    {
        $academyId = Auth::guard('academy')->id();

        $setting = AcademyAcademicSetting::updateOrCreate(
            ['academy_id' => $academyId],
            $request->validated()
        );

        return $this->sendResponse($setting->fresh(), __('message.academic_setting_updated'));
    }
}
