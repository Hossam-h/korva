<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Admin\ChangeAcademyStatusRequest;
use App\Http\Requests\Admin\UpdateAcademyRequest;
use App\Models\Academy;

class AcademyController extends BaseController
{
    /**
     * Show academy details.
     * GET /admin/academies/{academy}
     */
    public function show(Academy $academy)
    {
        $academy->load(['academicSetting', 'generalSetting', 'notificationSetting']);

        return $this->sendResponse($academy, 'Academy retrieved successfully');
    }

    /**
     * Update academy data.
     * PUT /admin/academies/{academy}
     */
    public function update(UpdateAcademyRequest $request, Academy $academy)
    {
        $academy->update($request->validated());

        $academy->load(['academicSetting', 'generalSetting', 'notificationSetting']);

        return $this->sendResponse($academy, 'Academy updated successfully');
    }

    /**
     * Change academy status (pending, approved, rejected).
     * PATCH /admin/academies/{academy}/status
     */
    public function changeStatus(ChangeAcademyStatusRequest $request, Academy $academy)
    {
        $academy->update(['status' => $request->status]);

        return $this->sendResponse($academy, 'Academy status changed successfully');
    }
}
