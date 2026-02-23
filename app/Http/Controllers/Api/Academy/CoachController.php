<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\StoreCoachRequest;
use App\Http\Requests\Academy\UpdateCoachRequest;
use App\Models\Coach;

class CoachController extends BaseController
{
    /**
     * List all coaches of the authenticated academy (auto-filtered by global scope).
     */
    public function index()
    {
        $coaches = Coach::with(['groups', 'licenses', 'tournaments'])->latest()->get();

        return $this->sendResponse($coaches, __('message.coaches_retrieved'));
    }

    /**
     * Create a new coach (academy_id auto-filled by BelongsToAcademy trait).
     */
    public function store(StoreCoachRequest $request)
    {
        $validated = $request->validated();

        $coach = Coach::create($validated);

        // Sync many-to-many groups
        if (isset($validated['group_ids'])) {
            $coach->groups()->syncWithoutDetaching($validated['group_ids']);
        }

        // Create licenses
        if (! empty($validated['licenses'])) {
            $coach->licenses()->createMany($validated['licenses']);
        }

        // Create tournaments
        if (! empty($validated['tournaments'])) {
            $coach->tournaments()->createMany($validated['tournaments']);
        }

        return $this->sendResponse(
            $coach->load(['groups', 'licenses', 'tournaments']),
            __('message.coach_created'),
            201
        );
    }

    /**
     * Show a single coach (scope ensures it belongs to the authenticated academy).
     */
    public function show($id)
    {
        $coach = Coach::with(['groups', 'licenses', 'tournaments'])->find($id);

        if (! $coach) {
            return $this->sendError(__('message.coach_not_found'), 404);
        }

        return $this->sendResponse($coach, __('message.coach_retrieved'));
    }

    /**
     * Update a coach.
     */
    public function update(UpdateCoachRequest $request, $id)
    {
        $coach = Coach::find($id);

        if (! $coach) {
            return $this->sendError(__('message.coach_not_found'), 404);
        }

        $validated = $request->validated();

        $coach->update($validated);

        // Sync many-to-many groups
        if (isset($validated['group_ids'])) {
            $coach->groups()->sync($validated['group_ids']);
        }

        // Replace licenses if provided
        if (isset($validated['licenses'])) {
            $coach->licenses()->delete();
            if (! empty($validated['licenses'])) {
                $coach->licenses()->createMany($validated['licenses']);
            }
        }

        // Replace tournaments if provided
        if (isset($validated['tournaments'])) {
            $coach->tournaments()->delete();
            if (! empty($validated['tournaments'])) {
                $coach->tournaments()->createMany($validated['tournaments']);
            }
        }

        return $this->sendResponse(
            $coach->fresh()->load(['groups', 'licenses', 'tournaments']),
            __('message.coach_updated')
        );
    }

    /**
     * Delete a coach.
     */
    public function destroy($id)
    {
        $coach = Coach::find($id);

        if (! $coach) {
            return $this->sendError(__('message.coach_not_found'), 404);
        }

        $coach->groups()->detach();
        $coach->delete();

        return $this->sendResponse(null, __('message.coach_deleted'));
    }
}
