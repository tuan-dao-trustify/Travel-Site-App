
## Summary

Deliver two functions for the hotel supplier integration:

1. **Hotel search:** a logged-in traveller searches hotels by city and dates and sees prices.
2. **Booking confirmation:** when the supplier tells us a booking is confirmed, we record it in our system exactly once.

## Background

We are launching hotel search on the travel site. Hotel stock and prices come from one external supplier, who exposes an HTTP API. The supplier is slow, limits how many calls we can make, and charges us per search.
When a traveller books, the supplier later tells us the booking is confirmed by calling us. The supplier sometimes tells us the same thing more than once.

The supplier documentation and a sandbox are in `FAKE_SUPPLIER.md`. Please treat it as a real third party.

## Function 1: Hotel search

**As a** logged-in traveller, **I want to** search for hotels by city and dates, **so that** I can compare prices.

### Requirements

1. A logged-in traveller can search by city, check-in date, check-out date and number of guests.
2. The results show hotel name and total price for the stay in the supplier's currency.
3. Searches must feel fast. Many travellers search the same popular cities and dates, and we do not want to pay the supplier for the same search again and again.
4. Prices must be exact (finance reconciles them).

### Rules

- Stay length: 1 to 30 nights. Check-out must be after check-in. Check-in cannot be in the past.
- Guests: 1 to 8.
- Prices shown may be up to 10 minutes old.

### Acceptance criteria
- Given I am not logged in, when I try to search, then I am asked to log in and I do not see any hotels.
- Given a valid search, when I submit it, then I see a list of hotels with prices.
- Given an invalid search (for example check-out before check-in), then I see a clear message saying what is wrong.
- Given the same search was made a moment ago, when I search again, then the result appears quickly and the supplier is not called again.

## Function 2: Booking confirmation from the supplier

**As** the business, **we want to** record a booking when the supplier confirms it, **so that** finance has one correct record per booking.

### Requirements

1. When the supplier sends us a booking confirmation, we store the booking in our system with status confirmed.
2. The supplier may send the same confirmation more than once. We must still end up with exactly one booking.
3. Amounts must be exact (finance reconciles them).

### Rules

- A booking is identified by the supplier's booking reference.
- The confirmation message format is described in `FAKE_SUPPLIER.md`.

### Acceptance criteria

- Given the supplier confirms booking `ABC123`, then one booking `ABC123` exists with status confirmed.
- Given the supplier sends the same confirmation again, then there is still only one booking `ABC123`.

## Out of scope

- Payments, user-facing booking form, cancellations, emails.
- Admin screens.
- What to show when the supplier is down or slow.
- Checking that a confirmation really came from the supplier.
- Real supplier contract (use the sandbox).