<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\StorePlayerRequest;
use App\Http\Requests\Academy\UpdatePlayerRequest;
use App\Models\Player;

class PlayerController extends BaseController
{
    /**
     * List all players of the authenticated academy.
     */
    public function index()
    {
        $players = Player::latest()->get();

        return $this->sendResponse($players, __('message.players_retrieved'));
    }

    /**
     * Create a new player.
     */
    public function store(StorePlayerRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);

        $player = Player::create($data);

        return $this->sendResponse($player, __('message.player_created'), 201);
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

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $player->update($data);

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
