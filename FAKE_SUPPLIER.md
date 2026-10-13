# Fake Supplier (for testing)

A stand-in for an external hotel supplier, for local development and tests. It is simulated inside this app by `routes/fake-supplier.php`. Treat it as a third party system: the app talks to it only over HTTP, the same way it would talk to the real one.

Config (already in `config/supplier.php`, values from `.env`): `SUPPLIER_BASE_URL`, `SUPPLIER_API_KEY`, `SUPPLIER_WEBHOOK_SECRET`, `SUPPLIER_WEBHOOK_URL`.

The local server must run with several workers (`PHP_CLI_SERVER_WORKERS=4` in `.env`) because the app calls the fake supplier on its own server.

## Search hotels

`GET {SUPPLIER_BASE_URL}/hotels?city=London&check_in=2027-03-01&check_out=2027-03-04&guests=2`

- Header `X-Api-Key: <SUPPLIER_API_KEY>` is required, otherwise 401.
- Responds after about 300 ms (`FAKE_SUPPLIER_DELAY_MS`).
- Prices are decimal strings in GBP, total for the whole stay.

```json
{
  "city": "London", "check_in": "2027-03-01", "check_out": "2027-03-04", "guests": 2,
  "results": [
    { "hotel_id": "H4A1B00", "name": "Grand Plaza London", "stars": 4, "total_price": "612.00", "currency": "GBP" }
  ]
}
```

```bash
curl -s -H "X-Api-Key: sandbox-key" \
  "http://localhost:8000/fake-supplier/v1/hotels?city=London&check_in=2027-03-01&check_out=2027-03-04&guests=2"
```

Failure simulation (add to the query string): `simulate=slow` (responds after 5 s), `simulate=error` (503), `simulate=rate_limit` (429 with `Retry-After: 5`).

```bash
curl -s -i -H "X-Api-Key: sandbox-key" "http://localhost:8000/fake-supplier/v1/hotels?city=London&check_in=2027-03-01&check_out=2027-03-04&guests=2&simulate=error"
curl -s -i -H "X-Api-Key: sandbox-key" "http://localhost:8000/fake-supplier/v1/hotels?city=London&check_in=2027-03-01&check_out=2027-03-04&guests=2&simulate=rate_limit"
```

Call counter (to check that caching works): `GET {SUPPLIER_BASE_URL}/stats` returns `{"search_calls": N}`, `DELETE {SUPPLIER_BASE_URL}/stats` resets it.

```bash
curl -s http://localhost:8000/fake-supplier/v1/stats             # {"search_calls":3}
curl -s -X DELETE http://localhost:8000/fake-supplier/v1/stats   # reset to 0
```

## Booking confirmation webhook (supplier calls us)

The supplier sends `POST` with a JSON body to our endpoint. `SUPPLIER_WEBHOOK_URL` is the URL of the endpoint in this app that receives it (the simulator command below posts there).

- Header `X-Supplier-Signature: sha256=<hex>`, where `<hex>` is HMAC-SHA256 of the raw request body using `SUPPLIER_WEBHOOK_SECRET`.
- The supplier retries, so the same event can arrive more than once.
- The receiving endpoint is not part of this repo yet. `POST /api/webhooks/supplier/booking-confirmed` is the URL the supplier is configured to call (and the default the simulator posts to), so this app must serve that path.

```json
{
  "event_id": "7f1c0a3e-....",
  "type": "booking.confirmed",
  "booking_reference": "ABC123",
  "total_price": "612.00",
  "currency": "GBP",
  "confirmed_at": "2027-03-01T10:15:00+00:00"
}
```

Send one with curl (default secret `sandbox-webhook-secret`, change the URL to your endpoint):

```bash
BODY='{"event_id":"evt-1","type":"booking.confirmed","booking_reference":"ABC123","total_price":"612.00","currency":"GBP","confirmed_at":"2027-03-01T10:15:00+00:00"}'
SIG=$(printf '%s' "$BODY" | openssl dgst -sha256 -hmac "sandbox-webhook-secret" | sed 's/^.* //')

curl -i -X POST "http://localhost:8000/api/webhooks/supplier/booking-confirmed" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "X-Supplier-Signature: sha256=$SIG" \
  -d "$BODY"
```

Or let the app do it for you:

```bash
php artisan supplier:send-confirmation ABC123                 # one valid event
php artisan supplier:send-confirmation ABC123 --times=2 --event-id=evt-1   # same event twice
php artisan supplier:send-confirmation ABC123 --bad-signature # wrong signature, the receiver should reject it
```
