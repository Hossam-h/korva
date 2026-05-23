<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\StorePlayerRequest;
use App\Http\Requests\Academy\UpdatePlayerRequest;
use App\Models\Player;
use Illuminate\Http\Request;

class PlayerController extends BaseController
{
    /**
     * List all players of the authenticated academy.
     */
    public function index(Request $request)
    {
        $players = Player::withCount('bookings')
            ->filter()
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->sendPaginatedResponse($players, __('message.players_retrieved'));
    }

    /**
     * Create a new player.
     */
    public function store(StorePlayerRequest $request)
    {
        $data = $request->validated();

        $image = $request->file('image');
        unset($data['image']);

        $data['password'] = bcrypt($data['password']);

        $player = Player::create($data);

        if ($image) {
            $player->uploadFile($image, 'image');
        }

        return $this->sendResponse($player->fresh(), __('message.player_created'), 201);
    }

    /**
     * Show a single player.
     */
    public function show($id)
    {
        $player = Player::find($id);

        if (!$player) {
            return $this->sendError(__('message.player_not_found'), 404);
        }

        return $this->sendResponse($player, __('message.player_retrieved'));
    }

    /**
     * Update a player.
     */
    public function update(UpdatePlayerRequest $request, $id)
    {
        $player = Player::find($id);

        if (!$player) {
            return $this->sendError(__('message.player_not_found'), 404);
        }

        $data = $request->validated();

        $image = $request->file('image');
        unset($data['image']);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $player->update($data);

        if ($image) {
            $player->uploadFile($image, 'image');
        }

        return $this->sendResponse($player->fresh(), __('message.player_updated'));
    }

    /**
     * Delete a player.
     */
    public function destroy($id)
    {
        $player = Player::find($id);

        if (!$player) {
            return $this->sendError(__('message.player_not_found'), 404);
        }

        $player->delete();

        return $this->sendResponse(null, __('message.player_deleted'));
    }
}
