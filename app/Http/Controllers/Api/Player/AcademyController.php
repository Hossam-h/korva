<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Player\AcademySearchRequest;
use App\Http\Requests\Player\PlayerReviewAcademyRequest;
use App\Http\Requests\Player\PlayerReviewCoachRequest;
use App\Http\Resources\Player\AcademyCoachResource;
use App\Http\Resources\Player\AcademyDetailResource;
use App\Http\Resources\Player\AcademyGroupResource;
use App\Http\Resources\Player\AcademyResource;
use App\Http\Resources\Player\AcademyReviewResource;
use App\Http\Resources\Player\AcademyServiceResource;
use App\Models\Academy;
use App\Models\Coach;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcademyController extends BaseController
{
    public function __construct(private readonly BookingService $bookingService) {}

    /**
     * Search academies by name, city, country, or age_group.
     *
     * Query params:
     *  - q          : search keyword (matches name, city, country, address)
     *  - city       : filter by exact city
     *  - country    : filter by exact country
     *  - age_group  : filter by age group
     *  - rating     : minimum average rating
     *  - latitude   : viewer latitude (optional; falls back to player profile)
     *  - longitude  : viewer longitude (optional; falls back to player profile)
     *  - per_page   : results per page (default 15)
     *
     * @return JsonResponse
     */
    public function search(AcademySearchRequest $request)
    {
        $playerId = auth('player')->id();

        $query = Academy::query()
            ->withAvg('reviews', 'rating')
            ->withCount(['reviews', 'bookings'])
            ->withMin('groups', 'monthly_price')
            ->withExists([
                'favoritedBy as is_favorite' => function ($q) use ($playerId) {
                    $q->where('player_id', $playerId);
                },
            ]);

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

        if ($request->filled('training_days')) {
            $days = $request->input('training_days');
            $query->whereHas('groups', function ($groupQuery) use ($days) {
                $groupQuery->where(function ($dayQuery) use ($days) {
                    foreach ($days as $day) {
                        $dayQuery->orWhereJsonContains('days', $day);
                    }
                });
            });
        }

        if ($request->filled('training_time')) {
            $query->whereHas('groups', function ($groupQuery) use ($request) {
                match ($request->training_time) {
                    'morning' => $groupQuery->whereTime('start_time', '<', '12:00:00'),
                    'afternoon' => $groupQuery
                        ->whereTime('start_time', '>=', '12:00:00')
                        ->whereTime('start_time', '<', '17:00:00'),
                    'evening' => $groupQuery->whereTime('start_time', '>=', '17:00:00'),
                };
            });
        }

        if ($request->filled('accepted_genders')) {
            $genders = $request->input('accepted_genders');
            $query->where(function ($genderQuery) use ($genders) {
                foreach ($genders as $gender) {
                    $genderQuery->orWhereJsonContains('accepted_genders', $gender);
                }
            });
        }

        if ($request->filled('min_age')) {
            $query->where(function ($ageQuery) use ($request) {
                $ageQuery->whereNull('max_age')->orWhere('max_age', '>=', $request->integer('min_age'));
            });
        }

        if ($request->filled('max_age')) {
            $query->where(function ($ageQuery) use ($request) {
                $ageQuery->whereNull('min_age')->orWhere('min_age', '<=', $request->integer('max_age'));
            });
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query
                ->when($request->filled('min_price'), fn ($q) => $q->having('groups_min_monthly_price', '>=', $request->input('min_price')))
                ->when($request->filled('max_price'), fn ($q) => $q->having('groups_min_monthly_price', '<=', $request->input('max_price')));
        }

        match ($request->input('sort', 'all')) {
            'most_popular' => $query->orderByDesc('bookings_count'),
            'top_rated' => $query->orderByDesc('reviews_avg_rating'),
            'newest', 'all' => $query->latest(),
        };

        $perPage = $request->input('per_page', 15);
        $academies = $query->paginate($perPage);
        $academies->through(fn (Academy $academy) => (new AcademyResource($academy))->resolve());

        return $this->sendPaginatedResponse($academies, 'Academies retrieved successfully');
    }

    /**
     * Show a single academy.
     *
     * @return JsonResponse
     */
    public function show(Request $request, Academy $academy)
    {
        $playerId = auth('player')->id();

        $academy->load(['attaches', 'operatingHours']);
        $academy->loadAvg('reviews', 'rating');
        $academy->loadCount([
            'reviews',
            'coaches',
            'groups',
            'services' => fn ($query) => $query->where('is_active', true),
        ]);
        $academy->loadMin('groups', 'monthly_price');
        $academy->loadExists([
            'favoritedBy as is_favorite' => function ($q) use ($playerId) {
                $q->where('player_id', $playerId);
            },
        ]);

        return $this->sendResponse(new AcademyDetailResource($academy), 'Academy retrieved successfully');
    }

    public function coaches(Academy $academy)
    {
        $coaches = $academy->coaches()
            ->with(['groups.players'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();

        return $this->sendResponse(
            AcademyCoachResource::collection($coaches),
            'Academy coaches retrieved successfully'
        );
    }

    public function services(Academy $academy)
    {
        $services = $academy->services()->where('is_active', true)->latest()->get();

        return $this->sendResponse(
            AcademyServiceResource::collection($services),
            'Academy services retrieved successfully'
        );
    }

    public function reviews(Request $request, Academy $academy)
    {
        $reviews = $academy->reviews()
            ->with('player')
            ->latest()
            ->paginate($request->input('per_page', 15));
        $reviews->through(fn ($review) => (new AcademyReviewResource($review))->resolve());

        return $this->sendResponse(
            $reviews->items(),
            'Academy reviews retrieved successfully',
            [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'from' => $reviews->firstItem(),
                'to' => $reviews->lastItem(),
                'reviews_avg_rating' => round((float) $academy->reviews()->avg('rating'), 2),
                'reviews_count' => $academy->reviews()->count(),
            ]
        );
    }

    public function groups(Academy $academy)
    {
        $groups = $academy->groups()->with(['coaches', 'academy'])->get();
        $groups->each(function ($group) {
            $group->setAttribute('available_seats', $this->bookingService->availableSeats($group));
        });

        return $this->sendResponse(
            AcademyGroupResource::collection($groups),
            'Academy groups retrieved successfully'
        );
    }

    public function favorites(Request $request)
    {
        $player = auth('player')->user();
        $academies = $player->favoriteAcademies()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withMin('groups', 'monthly_price')
            ->paginate($request->input('per_page', 15));
        $academies->through(function (Academy $academy) {
            $academy->setAttribute('is_favorite', true);

            return (new AcademyResource($academy))->resolve();
        });

        return $this->sendPaginatedResponse($academies, 'Favorite academies retrieved successfully');
    }

    /**
     * Add a review for an academy.
     *
     * @return JsonResponse
     */
    public function addReview(PlayerReviewAcademyRequest $request, Academy $academy)
    {
        $player = auth('player')->user();
        $existingReview = $academy->reviews()->where('player_id', $player->id)->first();
        $images = $existingReview?->images ?? [];

        if ($request->hasFile('images')) {
            collect($images)->each(fn ($path) => Storage::delete($path));
            $images = collect($request->file('images'))
                ->map(fn ($image) => $image->store('academy_reviews'))
                ->all();
        }

        // Use updateOrCreate so a player can only leave one review, and updating it will overwrite it
        $review = $academy->reviews()->updateOrCreate(
            ['player_id' => $player->id],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'images' => $images,
            ]
        );

        $review->load('player');
        $data = (new AcademyReviewResource($review))->resolve();
        $data['academy_rating_summary'] = [
            'reviews_avg_rating' => round((float) $academy->reviews()->avg('rating'), 2),
            'reviews_count' => $academy->reviews()->count(),
        ];

        return $this->sendResponse($data, 'Review submitted successfully');
    }

    public function reviewCoach(PlayerReviewCoachRequest $request, Coach $coach)
    {
        $review = $coach->reviews()->updateOrCreate(
            ['player_id' => auth('player')->id()],
            $request->validated()
        );

        return $this->sendResponse([
            'id' => $review->id,
            'coach_id' => $coach->id,
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'created_at' => $review->created_at,
            'coach_rating_summary' => [
                'reviews_avg_rating' => round((float) $coach->reviews()->avg('rating'), 2),
                'reviews_count' => $coach->reviews()->count(),
            ],
        ], 'Coach review submitted successfully');
    }

    /**
     * Favorite an academy.
     *
     * @return JsonResponse
     */
    public function favorite(Academy $academy)
    {
        $player = auth('player')->user();
        $player->favoriteAcademies()->syncWithoutDetaching([$academy->id]);

        return $this->sendResponse(
            ['academy_id' => $academy->id, 'is_favorite' => true],
            'Favorite status updated'
        );
    }

    /**
     * Remove an academy from favorites.
     *
     * @return JsonResponse
     */
    public function unfavorite(Academy $academy)
    {
        $player = auth('player')->user();
        $player->favoriteAcademies()->detach($academy->id);

        return $this->sendResponse(
            ['academy_id' => $academy->id, 'is_favorite' => false],
            'Favorite status updated'
        );
    }
}
