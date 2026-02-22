<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\StoreFieldRequest;
use App\Http\Requests\Academy\UpdateFieldRequest;
use App\Models\Field;

class FieldController extends BaseController
{
    /**
     * List all fields of the authenticated academy (auto-filtered by global scope).
     */
    public function index()
    {
        $fields = Field::latest()->get();

        return $this->sendResponse($fields, __('message.fields_retrieved'));
    }

    /**
     * Create a new field (academy_id auto-filled by BelongsToAcademy trait).
     */
    public function store(StoreFieldRequest $request)
    {
        $field = Field::create($request->validated());

        return $this->sendResponse($field, __('message.field_created'), 201);
    }

    /**
     * Show a single field (scope ensures it belongs to the authenticated academy).
     */
    public function show($id)
    {
        $field = Field::find($id);

        if (!$field) {
            return $this->sendError(__('message.field_not_found'), 404);
        }

        return $this->sendResponse($field, __('message.field_retrieved'));
    }

    /**
     * Update a field.
     */
    public function update(UpdateFieldRequest $request, $id)
    {
        $field = Field::find($id);

        if (!$field) {
            return $this->sendError(__('message.field_not_found'), 404);
        }

        $field->update($request->validated());

        return $this->sendResponse($field->fresh(), __('message.field_updated'));
    }

    /**
     * Delete a field.
     */
    public function destroy($id)
    {
        $field = Field::find($id);

        if (!$field) {
            return $this->sendError(__('message.field_not_found'), 404);
        }

        $field->delete();

        return $this->sendResponse(null, __('message.field_deleted'));
    }
}
