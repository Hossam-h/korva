<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\UpdateAcademyGeneralSettingRequest;
use App\Models\AcademyGeneralSetting;
use Illuminate\Support\Facades\Auth;

class AcademyGeneralSettingController extends BaseController
{
    /**
     * Update the general setting for the authenticated academy.
     * Uses updateOrCreate so it works even if no record exists yet.
     */
    public function update(UpdateAcademyGeneralSettingRequest $request)
    {
        $academyId = Auth::guard('academy')->id();

        $setting = AcademyGeneralSetting::updateOrCreate(
            ['academy_id' => $academyId],
            $request->validated()
        );

        return $this->sendResponse($setting->fresh(), __('message.general_setting_updated'));
    }
}
