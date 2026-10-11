<?php

namespace App\Http\Controllers\FakeSupplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class HotelSearchController extends Controller
{
    private const HOTEL_NAMES = [
        'Grand Plaza', 'Riverside Inn', 'The Royal Crown', 'Harbour View', 'City Lights Suites',
        'Old Town Lodge', 'Park Lane Hotel', 'Sunrise Boutique',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        abort_unless(config('supplier.fake.enabled'), 404);

        if ($request->header('X-Api-Key') !== config('supplier.api_key')) {
            return response()->json(['error' => 'invalid_api_key'], 401);
        }

        // add() first: increment() on a missing key does nothing with some stores (e.g. database)
        Cache::add(StatsController::COUNTER_KEY, 0);
        Cache::increment(StatsController::COUNTER_KEY);

        // Optional failure simulation: ?simulate=slow|error|rate_limit
        $simulate = $request->query('simulate');

        if ($simulate === 'error') {
            return response()->json(['error' => 'supplier_unavailable'], 503);
        }

        if ($simulate === 'rate_limit') {
            return response()->json(['error' => 'rate_limited'], 429, ['Retry-After' => '5']);
        }

        usleep(($simulate === 'slow' ? 5000 : (int) config('supplier.fake.delay_ms')) * 1000);

        $city = trim((string) $request->query('city'));
        $checkIn = Carbon::parse((string) $request->query('check_in'));
        $checkOut = Carbon::parse((string) $request->query('check_out'));
        $guests = max(1, (int) $request->query('guests', 1));
        $nights = max(1, $checkIn->diffInDays($checkOut));

        $hotels = [];

        foreach (self::HOTEL_NAMES as $index => $name) {
            $seed = crc32(strtolower($city).'|'.$index);
            $nightly = 60 + ($seed % 190) + ($guests > 2 ? 25 * ($guests - 2) : 0);

            $hotels[] = [
                'hotel_id' => sprintf('H%s%02d', strtoupper(substr(md5(strtolower($city)), 0, 4)), $index),
                'name' => $name.' '.ucfirst($city),
                'stars' => 2 + ($seed % 4),
                'total_price' => number_format($nightly * $nights, 2, '.', ''),
                'currency' => 'GBP',
            ];
        }

        return response()->json([
            'city' => $city,
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'guests' => $guests,
            'results' => $hotels,
        ]);
    }
}
