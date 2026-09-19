<?php

namespace App\Nexus\Modules\Currency\Services;

use App\Nexus\Modules\Currency\Enums\CurrencySymbolPosition;
use App\Nexus\Modules\Currency\Models\Currency;

class CurrencyService
{
    public static int $roundPosition = 4;

    /**
     * Converts a price from the base currency into the given currency.
     */
    public static function convertToTargetCurrency(float|int|string $price, Currency $currency): float
    {
        $price = (float) $price;

        if ($currency->is_base) {
            return round($price, self::$roundPosition);
        }

        return round($price * (float) $currency->rate, self::$roundPosition);
    }

    /**
     * Converts a price from the base currency into the currency selected in the session.
     */
    public static function convertToSessionCurrency(float|int|string $price): float
    {
        $currency = static::getSelectedCurrency();

        if ($currency === null) {
            return round((float) $price, self::$roundPosition);
        }

        return static::convertToTargetCurrency($price, $currency);
    }

    /**
     * Converts a price expressed in the currently-selected currency back
     * into the base currency — the inverse of convertToSessionCurrency(),
     * needed wherever a user enters an amount in their own currency (e.g.
     * the catalog price filter) that then has to be compared against
     * ShopProduct::price, which is always stored in the base currency.
     */
    public static function convertFromSessionCurrency(float|int|string $price): float
    {
        $currency = static::getSelectedCurrency();
        $price = (float) $price;

        if ($currency === null || $currency->is_base) {
            return round($price, self::$roundPosition);
        }

        return round($price / (float) $currency->rate, self::$roundPosition);
    }

    /**
     * Returns the currency selected in the session (an explicit manual
     * choice, e.g. via a future currency switcher), falling back to the
     * current locale's own currency, and finally to the base currency.
     */
    public static function getSelectedCurrency(): ?Currency
    {
        $code = session('currency');

        if ($code) {
            $currency = Currency::query()->publish()->where('code', $code)->first();

            if ($currency) {
                return $currency;
            }
        }

        return self::getCurrencyForLocale(app()->getLocale());
    }

    public static function getBaseCurrency(): ?Currency
    {
        return Currency::query()->where('is_base', true)->first();
    }

    /**
     * The storefront's currency for a given locale — English shows USD,
     * every other locale (just Ukrainian today) shows the base currency.
     */
    public static function getCurrencyForLocale(string $locale): ?Currency
    {
        $code = match ($locale) {
            'en' => 'USD',
            default => null,
        };

        if ($code === null) {
            return self::getBaseCurrency();
        }

        return Currency::query()->publish()->where('code', $code)->first() ?? self::getBaseCurrency();
    }

    /**
     * Converts a base-currency price into the current locale's currency and
     * formats it with its symbol, matching the storefront's existing
     * no-decimals price convention. Falls back to a plain number if no
     * currencies are configured yet (fresh install before seeding).
     */
    public static function formatForDisplay(float|int|string $basePrice): string
    {
        $currency = self::getSelectedCurrency();

        if ($currency === null) {
            return number_format((float) $basePrice, 0, '.', ' ');
        }

        $formatted = number_format(self::convertToTargetCurrency($basePrice, $currency), 0, '.', ' ');

        return $currency->symbol_position === CurrencySymbolPosition::BEFORE->value
            ? $currency->symbol.$formatted
            : $formatted.' '.$currency->symbol;
    }
}
