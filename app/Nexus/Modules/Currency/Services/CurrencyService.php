<?php

namespace App\Nexus\Modules\Currency\Services;

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
     * Returns the currency selected in the session, falling back to the base currency.
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

        return self::getBaseCurrency();
    }

    public static function getBaseCurrency(): ?Currency
    {
        return Currency::query()->where('is_base', true)->first();
    }
}
