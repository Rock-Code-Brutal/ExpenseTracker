<?php

use App\Http\Controllers\Api\TransactionController;

it('imports US dollar amounts without losing cents', function ($input, $expected) {
    $method = new ReflectionMethod(TransactionController::class, 'parseImportAmount');
    expect($method->invoke(new TransactionController, $input, 'USD'))->toBe($expected);
})->with([
    ['12.50', 12.5], ['$1,234.56', 1234.56], ['1,234.56', 1234.56],
    ['1234', 1234.0], ['1,23', false], ['abc', false],
]);

it('prioritizes US dates and supports ISO dates', function ($input, $expected) {
    $method = new ReflectionMethod(TransactionController::class, 'parseImportDate');
    expect($method->invoke(new TransactionController, $input))->toBe($expected);
})->with([
    ['04/05/2026', '2026-04-05'], ['12/31/2026', '2026-12-31'],
    ['2026-01-01', '2026-01-01'], ['02/30/2026', false],
]);
