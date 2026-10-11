<?php

beforeEach(function () {
    config(['supplier.fake.delay_ms' => 0]);
});

function supplierSearch(array $query = [], ?string $key = 'sandbox-key')
{
    return test()->withHeaders($key ? ['X-Api-Key' => $key] : [])->getJson('/fake-supplier/v1/hotels?'.http_build_query([
        'city' => 'London', 'check_in' => '2027-03-01', 'check_out' => '2027-03-04', 'guests' => 2, ...$query,
    ]));
}

test('supplier rejects a missing or wrong api key', function () {
    supplierSearch(key: null)->assertUnauthorized();
    supplierSearch(key: 'nope')->assertUnauthorized();
});

test('supplier returns deterministic hotels with decimal string prices', function () {
    $first = supplierSearch()->assertOk()->json('results');
    $second = supplierSearch()->json('results');

    expect($first)->toHaveCount(8)->and($second)->toBe($first);
    expect($first[0])->toHaveKeys(['hotel_id', 'name', 'stars', 'total_price', 'currency']);
    expect($first[0]['total_price'])->toMatch('/^\d+\.\d{2}$/');
});

test('supplier price depends on the number of nights', function () {
    $short = supplierSearch(['check_out' => '2027-03-02'])->json('results.0.total_price');
    $long = supplierSearch(['check_out' => '2027-03-05'])->json('results.0.total_price');

    expect((float) $long)->toBe((float) $short * 4);
});

test('supplier can simulate failures', function () {
    supplierSearch(['simulate' => 'error'])->assertStatus(503);
    supplierSearch(['simulate' => 'rate_limit'])->assertStatus(429)->assertHeader('Retry-After', '5');
});

test('supplier counts search calls', function () {
    $this->deleteJson('/fake-supplier/v1/stats');

    supplierSearch();
    supplierSearch();

    $this->getJson('/fake-supplier/v1/stats')->assertJson(['search_calls' => 2]);
});
