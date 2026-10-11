<?php

use App\Http\Controllers\FakeSupplier\HotelSearchController;
use App\Http\Controllers\FakeSupplier\StatsController;
use Illuminate\Support\Facades\Route;

/*
| A stand-in for the third party hotel supplier. Stateless: no session, no CSRF.
| Treat it as someone else's system. See FAKE_SUPPLIER.md.
*/
Route::prefix('fake-supplier/v1')->group(function () {
    Route::get('hotels', HotelSearchController::class);
    Route::get('stats', [StatsController::class, 'show']);
    Route::delete('stats', [StatsController::class, 'reset']);
});
