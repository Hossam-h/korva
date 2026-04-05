<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Player\PlayerReviewAcademyRequest;
use App\Models\Academy;
use Illuminate\Http\Request;

class AcademyController extends BaseController
{
    /**
     * Search academies by name, city, country, or age_group.
     *
     * Query params:
     *  - q          : search keyword (matches name, city, country, address)
     *  - city       : filter by exact city
     *  - country    : filter by exact country
     *  - age_group  : filter by age group
     *  - per_page   : results per page (default 15)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = Academy::withAvg('reviews', 'rating');

        // General keyword search across multiple fields
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('city', 'like', "%{$keyword}%")
                  ->orWhere('country', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        // Exact filters
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('age_group')) {
            $query->where('age_group', $request->age_group);
        }

        if ($request->filled('rating')) {
            $query->having('reviews_avg_rating', '>=', $request->rating);
        }

        $perPage = $request->input('per_page', 15);
        $academies = $query->latest()->paginate($perPage);

        return $this->sendResponse($academies, 'Academies retrieved successfully');
    }

    /**
     * Show a single academy.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Academy $academy)
    {academies
        $academy->load('groups', 'coaches');
        $academy->loadAvg('reviews', 'rating');
        return $this->sendResponse($academy, 'Academy retrieved successfully');
    }

    /**
     * Add a review for an academy.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addReview(PlayerReviewAcademyRequest $request, Academy $academy)
    {
        $player = auth('player')->user();

        // Use updateOrCreate so a player can only leave one review, and updating it will overwrite it
        $review = $academy->reviews()->updateOrCreate(
            ['player_id' => $player->id],
            [
                'rating'  => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return $this->sendResponse($review, __('message.review_added_successfully'));
    }
}
