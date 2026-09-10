<?php

namespace App\Nexus\Modules\Shipment\Enums;

/**
 * Normalized, carrier-agnostic delivery status. Mapped from Nova Poshta's
 * official StatusCode table (fromNovaPoshtaCode) or Ukrposhta's official
 * Status Tracking Service event-code table (fromUkrposhtaEventCode); any
 * code outside those tables falls back to free-text keyword matching
 * (guessFromText). Unmapped codes fall through to Unknown — the raw status
 * (raw_status/raw_status_code) is always kept alongside for diagnostics.
 */
enum DeliveryStatus: string
{
    case Unknown = 'unknown';
    case Registered = 'registered';
    case InTransit = 'in_transit';
    case AtWarehouse = 'at_warehouse';
    case Delivered = 'delivered';
    case Returning = 'returning';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    /**
     * Hardcoded (not routed through a translation namespace) — OrderShipment
     * isn't a registered Nexus module (see its own docblock), so it has no
     * `shipment::` lang namespace to resolve `__()` keys against.
     */
    public function label(): string
    {
        return match ($this) {
            self::Unknown => 'Статус невідомий',
            self::Registered => 'Зареєстровано',
            self::InTransit => 'В дорозі',
            self::AtWarehouse => 'У відділенні',
            self::Delivered => 'Вручено',
            self::Returning => 'Повертається',
            self::Returned => 'Повернено',
            self::Cancelled => 'Скасовано',
        };
    }

    /**
     * Nova Poshta's official StatusCode table. StatusCode 3 ("Номер не
     * знайдено") is deliberately excluded — that's not a delivery status,
     * it's an "unknown tracking number" error, which NovaPoshtaTracker
     * handles separately as a failed lookup before this mapping ever runs.
     */
    public static function fromNovaPoshtaCode(?string $code): self
    {
        return match ($code) {
            '1', '12' => self::Registered,
            '4', '41', '5', '6', '15', '101', '104', '112' => self::InTransit,
            '7', '8', '107', '111' => self::AtWarehouse,
            '9', '10', '11' => self::Delivered,
            '102', '103', '105', '106' => self::Returning,
            '2' => self::Cancelled,
            default => self::Unknown,
        };
    }

    /**
     * Ukrposhta's official Status Tracking Service event-code table
     * (dev.ukrposhta.ua). Grouped as:
     *   REGISTERED/CREATED  -> Registered (not yet in transit)
     *   DELIVERING          -> InTransit
     *   IN_DEPARTMENT       -> AtWarehouse
     *   STORAGE             -> AtWarehouse (handed off for storage)
     *   FORWARDING          -> InTransit (redirect/failed delivery attempt — still active)
     *   DELIVERED           -> Delivered
     *   RETURNED            -> Returned (handed back to sender)
     *   RETURNING           -> Returning
     *   CANCELLED           -> Cancelled (incl. 10603 "Deleted by client", no EcomAPI status listed)
     */
    public static function fromUkrposhtaEventCode(?string $code): self
    {
        return match ($code) {
            '10100', '10601' => self::Registered,
            '20700', '20800', '20900', '21500', '31100', '31300', '31400' => self::InTransit,
            '21700', '21400' => self::AtWarehouse,
            '41000', '48000' => self::Delivered,
            '41010' => self::Returned,
            '31200' => self::Returning,
            '10600', '10602', '10603' => self::Cancelled,
            default => self::Unknown,
        };
    }

    /**
     * Fallback for any code outside the official tables — matches keywords
     * in the free-text status.
     */
    public static function guessFromText(?string $text): self
    {
        if (! $text) {
            return self::Unknown;
        }

        $text = mb_strtolower($text);

        return match (true) {
            str_contains($text, 'скасован') || str_contains($text, 'відмін') => self::Cancelled,
            str_contains($text, 'повернен') || str_contains($text, 'поверта') => self::Returning,
            str_contains($text, 'вручен') || str_contains($text, 'отримано одержувачем') || str_contains($text, 'доставлен') => self::Delivered,
            str_contains($text, 'відділенн') || str_contains($text, 'поштомат') || str_contains($text, 'очіку') || str_contains($text, 'прибул') => self::AtWarehouse,
            str_contains($text, 'дорозі') || str_contains($text, 'прямує') || str_contains($text, 'рухається') => self::InTransit,
            str_contains($text, 'зареєстрован') || str_contains($text, 'прийнят') || str_contains($text, 'сформован') => self::Registered,
            default => self::Unknown,
        };
    }
}
