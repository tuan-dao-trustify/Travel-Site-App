<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hotel Supplier (third party)
    |--------------------------------------------------------------------------
    |
    | Connection details for the external hotel supplier. In local development
    | and tests the base URL points at the fake supplier bundled with this app
    | (routes/fake-supplier.php). See FAKE_SUPPLIER.md.
    |
    */

    'base_url' => env('SUPPLIER_BASE_URL', 'http://localhost:8000/fake-supplier/v1'),

    'api_key' => env('SUPPLIER_API_KEY', 'sandbox-key'),

    'webhook_secret' => env('SUPPLIER_WEBHOOK_SECRET', 'sandbox-webhook-secret'),

    'webhook_url' => env('SUPPLIER_WEBHOOK_URL', 'http://localhost:8000/api/webhooks/supplier/booking-confirmed'),

    /*
    |--------------------------------------------------------------------------
    | Fake supplier behaviour (local only)
    |--------------------------------------------------------------------------
    */

    'fake' => [
        'enabled' => env('FAKE_SUPPLIER_ENABLED', env('APP_ENV') !== 'production'),
        'delay_ms' => (int) env('FAKE_SUPPLIER_DELAY_MS', 300),
    ],

];
