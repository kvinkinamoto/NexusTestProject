<?php

use App\Nexus\Modules\Shipment\Services\Trackers\NovaPoshtaTracker;
use App\Nexus\Modules\Shipment\Services\Trackers\UkrPoshtaTracker;
use App\Nexus\Modules\Shipment\Services\TrackingManager;

it('recognizes the two trackable carriers', function () {
    expect(TrackingManager::isTrackable('nova-poshta'))->toBeTrue();
    expect(TrackingManager::isTrackable('ukr-poshta'))->toBeTrue();
    expect(TrackingManager::isTrackable('pickup'))->toBeFalse();
    expect(TrackingManager::isTrackable(null))->toBeFalse();
});

it('resolves the nova poshta tracker', function () {
    expect((new TrackingManager)->driver('nova-poshta'))->toBeInstanceOf(NovaPoshtaTracker::class);
});

it('resolves the ukrposhta tracker', function () {
    expect((new TrackingManager)->driver('ukr-poshta'))->toBeInstanceOf(UkrPoshtaTracker::class);
});

it('throws for an untrackable carrier', function () {
    (new TrackingManager)->driver('pickup');
})->throws(InvalidArgumentException::class);
