<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupplierSendConfirmation extends Command
{
    protected $signature = 'supplier:send-confirmation
        {reference : Supplier booking reference, e.g. ABC123}
        {--event-id= : Event id (defaults to a new uuid)}
        {--price=150.00 : Total price}
        {--currency=GBP}
        {--times=1 : Send the same event this many times (to test duplicates)}
        {--bad-signature : Send a wrong signature}
        {--url= : Target URL (defaults to config supplier.webhook_url)}';

    protected $description = 'Act as the supplier: send a signed booking confirmation webhook to this app';

    public function handle(): int
    {
        $payload = [
            'event_id' => $this->option('event-id') ?: (string) Str::uuid(),
            'type' => 'booking.confirmed',
            'booking_reference' => $this->argument('reference'),
            'total_price' => $this->option('price'),
            'currency' => $this->option('currency'),
            'confirmed_at' => now()->toIso8601String(),
        ];

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $secret = $this->option('bad-signature') ? 'wrong-secret' : config('supplier.webhook_secret');
        $signature = 'sha256='.hash_hmac('sha256', $body, $secret);
        $url = $this->option('url') ?: config('supplier.webhook_url');

        for ($i = 1; $i <= (int) $this->option('times'); $i++) {
            $response = Http::withBody($body, 'application/json')
                ->withHeaders(['X-Supplier-Signature' => $signature, 'Accept' => 'application/json'])
                ->post($url);

            $this->line("#{$i} {$url} -> HTTP {$response->status()}");
        }

        $this->info('event_id: '.$payload['event_id']);

        return self::SUCCESS;
    }
}
