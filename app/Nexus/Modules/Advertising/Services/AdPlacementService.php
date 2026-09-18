<?php

namespace App\Nexus\Modules\Advertising\Services;

use App\Nexus\Modules\Advertising\Models\Advertising;
use Nodex\Nexus\Services\SettingsBuilder;

/**
 * Picks one eligible ad for a slot, on every call (the AdvertisingSlot
 * widget deliberately declares no #[Widget(cacheTtl:)] so this runs fresh
 * per render): random order among a slot's active candidates, skipping any
 * that already hit its own global or per-visitor impression cap, recording
 * the impression on whichever ad wins as a side effect of picking it.
 */
class AdPlacementService
{
    public function pick(string $slot): ?Advertising
    {
        if (! SettingsBuilder::get('advertising', 'enabled', true)) {
            return null;
        }

        $visitorKey = session()->getId();

        $candidates = Advertising::query()
            ->forSlot($slot)
            ->active()
            ->inRandomOrder()
            ->get();

        foreach ($candidates as $ad) {
            if ($ad->hasReachedGlobalCap() || $ad->hasReachedVisitorCap($visitorKey)) {
                continue;
            }

            $ad->recordImpression($visitorKey);

            return $ad;
        }

        return null;
    }
}
