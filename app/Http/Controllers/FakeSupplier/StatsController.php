<?php

namespace App\Http\Controllers\FakeSupplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public const COUNTER_KEY = 'fake-supplier.search-calls';

    /** How many times the supplier search endpoint was called (to check caching works). */
    public function show(): JsonResponse
    {
        abort_unless(config('supplier.fake.enabled'), 404);

        return response()->json(['search_calls' => (int) Cache::get(self::COUNTER_KEY, 0)]);
    }

    public function reset(): JsonResponse
    {
        abort_unless(config('supplier.fake.enabled'), 404);

        Cache::forget(self::COUNTER_KEY);

        return response()->json(['search_calls' => 0]);
    }
}
