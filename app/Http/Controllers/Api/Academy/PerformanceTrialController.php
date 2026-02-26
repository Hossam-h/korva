<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\StorePerformanceTrialRequest;
use App\Http\Requests\Academy\UpdatePerformanceTrialRequest;
use App\Models\PerformanceTrial;

class PerformanceTrialController extends BaseController
{
    /**
     * List all performance trials of the authenticated academy (auto-filtered by global scope).
     */
    public function index()
    {
        $trials = PerformanceTrial::latest()->get();

        return $this->sendResponse($trials, __('message.performance_trials_retrieved'));
    }

    /**
     * Create a new performance trial (academy_id auto-filled by BelongsToAcademy trait).
     */
    public function store(StorePerformanceTrialRequest $request)
    {
        $data = $request->validated();

        // Remove thumbnail from mass-assignment; handle separately via trait
        $thumbnail = $request->file('thumbnail');
        unset($data['thumbnail']);

        $trial = PerformanceTrial::create($data);

        if ($thumbnail) {
            $trial->uploadFile($thumbnail, 'thumbnail');
        }

        return $this->sendResponse($trial->fresh(), __('message.performance_trial_created'), 201);
    }

    /**
     * Show a single performance trial (scope ensures it belongs to the authenticated academy).
     */
    public function show($id)
    {
        $trial = PerformanceTrial::find($id);

        if (!$trial) {
            return $this->sendError(__('message.performance_trial_not_found'), 404);
        }

        return $this->sendResponse($trial, __('message.performance_trial_retrieved'));
    }

    /**
     * Update a performance trial.
     */
    public function update(UpdatePerformanceTrialRequest $request, $id)
    {
        $trial = PerformanceTrial::find($id);

        if (!$trial) {
            return $this->sendError(__('message.performance_trial_not_found'), 404);
        }

        $data = $request->validated();

        // Remove thumbnail from mass-assignment; handle separately via trait
        $thumbnail = $request->file('thumbnail');
        unset($data['thumbnail']);

        $trial->update($data);

        if ($thumbnail) {
            $trial->uploadFile($thumbnail, 'thumbnail');
        }

        return $this->sendResponse($trial->fresh(), __('message.performance_trial_updated'));
    }

    /**
     * Delete a performance trial.
     */
    public function destroy($id)
    {
        $trial = PerformanceTrial::find($id);

        if (!$trial) {
            return $this->sendError(__('message.performance_trial_not_found'), 404);
        }

        $trial->delete(); // HasFileAttachment auto-deletes thumbnail on model delete

        return $this->sendResponse(null, __('message.performance_trial_deleted'));
    }
}
