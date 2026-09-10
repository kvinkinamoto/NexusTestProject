<?php

use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use App\Nexus\Modules\Shipment\Services\Trackers\UkrPoshtaTracker;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->tracker = new UkrPoshtaTracker;
});

it('returns a normalized delivered status', function () {
    Http::fake([
        '*/status-tracking/*' => Http::response([
            ['eventName' => 'Вручено', 'event' => '41000'],
        ], 200),
    ]);

    $result = $this->tracker->track('0300000000000');

    expect($result->success)->toBeTrue();
    expect($result->normalizedStatus)->toBe(DeliveryStatus::Delivered);
    expect($result->rawStatusCode)->toBe('41000');
});

it('fails gracefully on an empty result', function () {
    Http::fake([
        '*/status-tracking/*' => Http::response([], 200),
    ]);

    $result = $this->tracker->track('0300000000000');

    expect($result->success)->toBeFalse();
    expect($result->normalizedStatus)->toBe(DeliveryStatus::Unknown);
});

it('fails gracefully when the API call fails', function () {
    Http::fake([
        '*/status-tracking/*' => Http::response([], 500),
    ]);

    $result = $this->tracker->track('0300000000000');

    expect($result->success)->toBeFalse();
});
