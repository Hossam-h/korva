<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\StoreGroupRequest;
use App\Http\Requests\Academy\UpdateGroupRequest;
use App\Models\Group;

class GroupController extends BaseController
{
    /**
     * List all groups of the authenticated academy (auto-filtered by global scope).
     */
    public function index()
    {
        $groups = Group::with('field')->latest()->get();

        return $this->sendResponse($groups, __('message.groups_retrieved'));
    }

    /**
     * Create a new group (academy_id auto-filled by BelongsToAcademy trait).
     */
    public function store(StoreGroupRequest $request)
    {
        $group = Group::create($request->validated());

        return $this->sendResponse(
            $group->load('field'),
            __('message.group_created'),
            201
        );
    }

    /**
     * Show a single group (scope ensures it belongs to the authenticated academy).
     */
    public function show($id)
    {
        $group = Group::with('field')->find($id);

        if (!$group) {
            return $this->sendError(__('message.group_not_found'), 404);
        }

        return $this->sendResponse($group, __('message.group_retrieved'));
    }

    /**
     * Update a group.
     */
    public function update(UpdateGroupRequest $request, $id)
    {
        $group = Group::find($id);

        if (!$group) {
            return $this->sendError(__('message.group_not_found'), 404);
        }

        $group->update($request->validated());

        return $this->sendResponse(
            $group->fresh()->load('field'),
            __('message.group_updated')
        );
    }

    /**
     * Delete a group.
     */
    public function destroy($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return $this->sendError(__('message.group_not_found'), 404);
        }

        $group->delete();

        return $this->sendResponse(null, __('message.group_deleted'));
    }
}
