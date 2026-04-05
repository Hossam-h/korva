<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeospatialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeospatialController extends Controller
{
    public function __construct(
        protected GeospatialService $geo,
    ) {}

    /**
     * POST /api/geo/drivers/{driver}/location
     * Body: { "lat": 30.0444, "lng": 31.2357 }
     */
    public function updateDriverLocation(Request $request, string $driver): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $added = $this->geo->driverUpdateLocation($driver, $request->lat, $request->lng);

        return response()->json([
            'message' => $added ? 'Driver location added' : 'Driver location updated',
            'driver'  => "driver:{$driver}",
            'lat'     => $request->lat,
            'lng'     => $request->lng,
        ]);
    }

    /**
     * GET /api/geo/drivers/nearest?lat=30.04&lng=31.23&radius=5&unit=km&count=3
     */
    public function findNearestDrivers(Request $request): JsonResponse
    {
        $request->validate([
            'lat'    => 'required|numeric|between:-90,90',
            'lng'    => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1',
            'unit'   => 'nullable|in:km,m,mi,ft',
            'count'  => 'nullable|integer|min:1|max:50',
        ]);

        $drivers = $this->geo->findNearestDrivers(
            userLat: $request->lat, // 30.0444
            userLng: $request->lng, // 31.2357
            radius:  $request->input('radius', 5), // that mean 5 km
            unit:    $request->input('unit', 'km'), // km, m, mi, ft
            count:   $request->input('count', 3), // how many drivers to return
        );

        return response()->json([
            'search' => [
                'lat'    => $request->lat,
                'lng'    => $request->lng,
                'radius' => $request->input('radius', 5),
                'unit'   => $request->input('unit', 'km'),
            ],
            'results'       => $drivers,
            'results_count' => count($drivers),
        ]);
    }

    /**
     * GET /api/geo/drivers/distance?from=driver:1&to=driver:2&unit=km
     */
    public function getDistance(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|string',
            'to'   => 'required|string',
            'unit' => 'nullable|in:km,m,mi,ft',
        ]);

        $distance = $this->geo->getDistance(
            $request->from,
            $request->to,
            $request->input('unit', 'km'),
        );

        return response()->json([
            'from'     => $request->from,
            'to'       => $request->to,
            'distance' => $distance,
        ]);
    }

    /**
     * GET /api/geo/drivers/{driver}/position
     */
    public function getDriverPosition(string $driver): JsonResponse
    {
        $positions = $this->geo->getPositions("driver:{$driver}");

        return response()->json([
            'driver'   => "driver:{$driver}",
            'position' => $positions["driver:{$driver}"],
        ]);
    }

    /**
     * DELETE /api/geo/drivers/{driver}
     */
    public function removeDriver(string $driver): JsonResponse
    {
        $removed = $this->geo->removeDriver($driver);

        return response()->json([
            'message' => $removed ? 'Driver removed' : 'Driver not found',
            'driver'  => "driver:{$driver}",
        ]);
    }

    /**
     * POST /api/geo/seed
     * Seeds test drivers around Cairo, Egypt
     */
    public function seedTestData(): JsonResponse
    {
        // Cairo, Egypt — central point ~30.0444, 31.2357
        $testDrivers = [
            ['id' => 1,  'name' => 'Ahmed',    'lat' => 30.0444, 'lng' => 31.2357],  // Tahrir Square
            ['id' => 2,  'name' => 'Mohamed',   'lat' => 30.0500, 'lng' => 31.2400],  // Downtown Cairo
            ['id' => 3,  'name' => 'Ali',       'lat' => 30.0600, 'lng' => 31.2200],  // Zamalek
            ['id' => 4,  'name' => 'Omar',      'lat' => 30.0300, 'lng' => 31.2100],  // Giza side
            ['id' => 5,  'name' => 'Hassan',    'lat' => 30.0700, 'lng' => 31.2500],  // Heliopolis area
            ['id' => 6,  'name' => 'Youssef',   'lat' => 30.0200, 'lng' => 31.2600],  // Maadi
            ['id' => 7,  'name' => 'Khaled',    'lat' => 30.0800, 'lng' => 31.2100],  // Mohandessin
            ['id' => 8,  'name' => 'Ibrahim',   'lat' => 30.0100, 'lng' => 31.2300],  // Old Cairo
            ['id' => 9,  'name' => 'Mahmoud',   'lat' => 30.0550, 'lng' => 31.2650],  // Nasr City
            ['id' => 10, 'name' => 'Tarek',     'lat' => 30.1200, 'lng' => 31.3400],  // Far — New Cairo (>10 km)
        ];

        foreach ($testDrivers as $driver) {
            $this->geo->driverUpdateLocation($driver['id'], $driver['lat'], $driver['lng']);
        }

        return response()->json([
            'message'       => '10 test drivers seeded around Cairo, Egypt',
            'drivers'       => $testDrivers,
            'hint'          => 'Try: GET /api/geo/drivers/nearest?lat=30.0444&lng=31.2357&radius=5&unit=km&count=5',
        ]);
    }

    /**
     * DELETE /api/geo/flush
     */
    public function flush(): JsonResponse
    {
        $this->geo->flushAll();

        return response()->json(['message' => 'All driver locations flushed']);
    }
}
