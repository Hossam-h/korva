<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\StoreCoachRequest;
use App\Http\Requests\Academy\UpdateCoachRequest;
use App\Models\Coach;
use Illuminate\Http\Request;

class CoachController extends BaseController
{
    /**
     * List all coaches of the authenticated academy (auto-filtered by global scope).
     */
    public function index(Request $request)
    {
        $coaches = Coach::with(['groups', 'licenses', 'tournaments'])
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->sendPaginatedResponse($coaches, __('message.coaches_retrieved'));
    }

    /**
     * Create a new coach (academy_id auto-filled by BelongsToAcademy trait).
     */
    public function store(StoreCoachRequest $request)
    {
        $validated = $request->validated();
        $image = $request->file('image');
        unset($validated['image'], $validated['group_ids'], $validated['licenses'], $validated['tournaments']);

        $coach = Coach::create($validated);

        if ($image) {
            $coach->uploadFile($image, 'image', 'coaches');
        }

        // Sync many-to-many groups
        if ($request->filled('group_ids')) {
            $coach->groups()->syncWithoutDetaching($request->input('group_ids'));
        }

        // Create licenses
        if ($request->filled('licenses')) {
            $coach->licenses()->createMany($request->input('licenses'));
        }

        // Create tournaments
        if ($request->filled('tournaments')) {
            $coach->tournaments()->createMany($request->input('tournaments'));
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
        $image = $request->file('image');
        unset($validated['image'], $validated['group_ids'], $validated['licenses'], $validated['tournaments']);

        $coach->update($validated);

        if ($image) {
            $coach->uploadFile($image, 'image', 'coaches');
        }

        // Sync many-to-many groups
        if ($request->has('group_ids')) {
            $coach->groups()->sync($request->input('group_ids'));
        }

        // Replace licenses if provided
        if ($request->has('licenses')) {
            $coach->licenses()->delete();
            if ($request->filled('licenses')) {
                $coach->licenses()->createMany($request->input('licenses'));
            }
        }

        // Replace tournaments if provided
        if ($request->has('tournaments')) {
            $coach->tournaments()->delete();
            if ($request->filled('tournaments')) {
                $coach->tournaments()->createMany($request->input('tournaments'));
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
