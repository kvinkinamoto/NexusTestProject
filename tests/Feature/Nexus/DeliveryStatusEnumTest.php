<?php

use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;

it('maps known Nova Poshta status codes', function (string $code, DeliveryStatus $expected) {
    expect(DeliveryStatus::fromNovaPoshtaCode($code))->toBe($expected);
})->with([
    ['1', DeliveryStatus::Registered],
    ['41', DeliveryStatus::InTransit],
    ['7', DeliveryStatus::AtWarehouse],
    ['9', DeliveryStatus::Delivered],
    ['102', DeliveryStatus::Returning],
    ['2', DeliveryStatus::Cancelled],
]);

it('falls back to Unknown for an unmapped Nova Poshta code', function () {
    expect(DeliveryStatus::fromNovaPoshtaCode('999'))->toBe(DeliveryStatus::Unknown);
    expect(DeliveryStatus::fromNovaPoshtaCode(null))->toBe(DeliveryStatus::Unknown);
});

it('maps known Ukrposhta event codes', function (string $code, DeliveryStatus $expected) {
    expect(DeliveryStatus::fromUkrposhtaEventCode($code))->toBe($expected);
})->with([
    ['10100', DeliveryStatus::Registered],
    ['20700', DeliveryStatus::InTransit],
    ['21700', DeliveryStatus::AtWarehouse],
    ['41000', DeliveryStatus::Delivered],
    ['41010', DeliveryStatus::Returned],
    ['31200', DeliveryStatus::Returning],
    ['10600', DeliveryStatus::Cancelled],
]);

it('guesses a status from free text as a last resort', function (string $text, DeliveryStatus $expected) {
    expect(DeliveryStatus::guessFromText($text))->toBe($expected);
})->with([
    ['Відправлення скасовано', DeliveryStatus::Cancelled],
    ['Прямує до м. Київ', DeliveryStatus::InTransit],
    ['Прибуло до відділення', DeliveryStatus::AtWarehouse],
    ['Вручено одержувачу', DeliveryStatus::Delivered],
    ['Щось незрозуміле', DeliveryStatus::Unknown],
]);

it('has a Ukrainian label for every case', function () {
    foreach (DeliveryStatus::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }
});
