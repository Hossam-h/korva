<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Player\StoreChildRequest;
use App\Http\Requests\Player\UpdateChildRequest;
use App\Http\Resources\Player\ChildResource;
use App\Models\Player;

class ParentChildController extends BaseController
{
    public function index()
    {
        $parent = auth('player')->user();

        if ($parent->type !== 'parent') {
            return $this->sendNotAuthorized(null, 'Only parent accounts can manage children');
        }

        return $this->sendResponse(
            ChildResource::collection($parent->children()->oldest()->get()),
            'Children retrieved successfully'
        );
    }

    public function store(StoreChildRequest $request)
    {
        $data = $request->safe()->except('image');
        $data['parent_id'] = auth('player')->id();
        $data['type'] = 'player';
        $child = Player::create($data);

        if ($request->hasFile('image')) {
            $child->uploadFile($request->file('image'), 'image', 'players');
        }

        return $this->sendResponse(new ChildResource($child), 'Child created successfully');
    }

    public function update(UpdateChildRequest $request, int $child)
    {
        $childModel = $this->findOwnedChild($child);
        $childModel->update($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $childModel->uploadFile($request->file('image'), 'image', 'players');
        }

        return $this->sendResponse(new ChildResource($childModel->fresh()), 'Child updated successfully');
    }

    public function destroy(int $child)
    {
        $this->findOwnedChild($child)->delete();

        return $this->sendResponse(null, 'Child deleted successfully');
    }

    private function findOwnedChild(int $id): Player
    {
        return auth('player')->user()->children()->findOrFail($id);
    }
}
