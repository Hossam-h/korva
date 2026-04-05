<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class GeospatialService
{
    /**
     * Redis key for storing driver locations.
     */
    protected string $geoKey = 'drivers:locations';

    /**
     * ① Register / update a driver's location.
     *
     * @return int Number of elements added (0 if updated, 1 if new)
     */
    public function driverUpdateLocation(int|string $driverId, float $lat, float $lng): int
    {
        return Redis::geoadd($this->geoKey, $lng, $lat, "driver:{$driverId}");
    }

    /**
     * ② Find the nearest drivers to a given user location.
     *
     * @param  float  $radius  Search radius (default 5)
     * @param  string  $unit  Unit: km, m, mi, ft (default km)
     * @param  int  $count  Max results to return (default 3)
     * @return array List of matching driver members with distance info
     */
    public function findNearestDrivers(
        float $userLat,
        float $userLng,
        float $radius = 5,
        string $unit = 'km',
        int $count = 3
    ): array {
        // GEORADIUS returns members sorted ASC (nearest first)
        // WITHDIST  → include distance in the result
        // WITHCOORD → include coordinates in the result
        $results = Redis::georadius(
            $this->geoKey,
            $userLng,
            $userLat,
            $radius,
            $unit,
            'WITHCOORD',
            'WITHDIST',
            'ASC',
            'COUNT',
            $count
        );

        return collect($results)->map(function ($item) use ($unit) {
            return [
                'member' => $item[0],                          // e.g. "driver:5"
                'distance' => round((float) $item[1], 4)." {$unit}",
                'lng' => (float) $item[2][0],
                'lat' => (float) $item[2][1],
            ];
        })->toArray();
    }

    /**
     * ③ Get the distance between two drivers.
     *
     * @param  string  $member1  e.g. "driver:1"
     * @param  string  $member2  e.g. "driver:2"
     * @param  string  $unit  km | m | mi | ft
     * @return string|null Distance string or null if member(s) missing
     */
    public function getDistance(string $member1, string $member2, string $unit = 'km'): ?string
    {
        $distance = Redis::geodist($this->geoKey, $member1, $member2, $unit);

        return $distance !== null ? round((float) $distance, 4)." {$unit}" : null;
    }

    /**
     * ④ Get the position (lat/lng) of one or more drivers.
     *
     * @param  string  ...$members  e.g. "driver:1", "driver:2"
     */
    public function getPositions(string ...$members): array
    {

        $positions = Redis::geopos($this->geoKey, ...$members);

        $result = [];
        foreach ($members as $i => $member) {
            $result[$member] = $positions[$i]
                ? ['lng' => (float) $positions[$i][0], 'lat' => (float) $positions[$i][1]]
                : null;
        }

        return $result;
    }

    /**
     * ⑤ Remove a driver from the geo index.
     *
     * @return int Number of members removed
     */
    public function removeDriver(int|string $driverId): int
    {
        return Redis::zrem($this->geoKey, "driver:{$driverId}");
    }

    /**
     * ⑥ Flush all driver locations (useful for testing / reset).
     */
    public function flushAll(): int
    {
        return Redis::del($this->geoKey);
    }
}
