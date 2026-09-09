{{--
    Usable from any page/theme via `@include('currency::public.currency-price', ['amount' => $price])`
    — not a `<x-nexus::...>` tag component: that namespace belongs to the
    package (packages/nodex/nexus), which must not depend on an app-level
    module's Eloquent model.

    $amount   — price already expressed in the target currency (e.g. via
                CurrencyService::convertToSessionCurrency($basePrice))
    $currency — a Currency model to format with; defaults to the
                session-selected currency (falls back to the base currency)
                when omitted
--}}
@php
    $resolvedCurrency = $currency ?? \App\Nexus\Modules\Currency\Services\CurrencyService::getSelectedCurrency();

    $symbol = $resolvedCurrency?->symbol ?? '';
    $position = $resolvedCurrency?->symbol_position ?? \App\Nexus\Modules\Currency\Enums\CurrencySymbolPosition::AFTER->value;

    $formatted = number_format((float) $amount, 2, '.', ' ');
@endphp
@if ($position === \App\Nexus\Modules\Currency\Enums\CurrencySymbolPosition::BEFORE->value){{ $symbol }}{{ $formatted }}@else{{ $formatted }}&nbsp;{{ $symbol }}@endif
