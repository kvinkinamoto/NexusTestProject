<?php

use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use App\Nexus\Modules\Shipment\Services\Trackers\NovaPoshtaTracker;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->tracker = new NovaPoshtaTracker;
});

it('returns a normalized in-transit status', function () {
    Http::fake([
        'api.novaposhta.ua/*' => Http::response([
            'success' => true,
            'data' => [['Status' => 'Прямує до м. Київ', 'StatusCode' => '4']],
        ], 200),
    ]);

    $result = $this->tracker->track('20450000000000');

    expect($result->success)->toBeTrue();
    expect($result->normalizedStatus)->toBe(DeliveryStatus::InTransit);
    expect($result->rawStatusCode)->toBe('4');
});

it('treats StatusCode 3 as a failed lookup, not a delivery status', function () {
    Http::fake([
        'api.novaposhta.ua/*' => Http::response([
            'success' => true,
            'data' => [['Status' => 'Номер не знайдено', 'StatusCode' => '3']],
        ], 200),
    ]);

    $result = $this->tracker->track('00000000000000');

    expect($result->success)->toBeFalse();
    expect($result->normalizedStatus)->toBe(DeliveryStatus::Unknown);
});

it('fails gracefully when the API call itself fails', function () {
    Http::fake([
        'api.novaposhta.ua/*' => Http::response([], 500),
    ]);

    $result = $this->tracker->track('20450000000000');

    expect($result->success)->toBeFalse();
    expect($result->normalizedStatus)->toBe(DeliveryStatus::Unknown);
});

it('fails gracefully when the API reports an unsuccessful answer', function () {
    Http::fake([
        'api.novaposhta.ua/*' => Http::response(['success' => false, 'errors' => ['API key invalid']], 200),
    ]);

    $result = $this->tracker->track('20450000000000');

    expect($result->success)->toBeFalse();
});
