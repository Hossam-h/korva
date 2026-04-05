<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Api\BaseController;
use App\Models\Academy;
use App\Models\Coach;
use App\Models\PerformanceTrial;

class HomeController extends BaseController
{
    /**
     * Player home page data.
     *
     * Returns the authenticated player's profile along with
     * the latest 5 academies, coaches, and performance trials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $player = auth('player')->user();

        $data = [
            'player'             => $player,
            'latest_academies'   => Academy::latest()->take(5)->get(),
            'latest_coaches'     => Coach::latest()->take(5)->get(),
            'latest_trials'      => PerformanceTrial::latest()->take(5)->get(),
        ];

        return $this->sendResponse($data, 'Home data retrieved successfully');
    }
}
