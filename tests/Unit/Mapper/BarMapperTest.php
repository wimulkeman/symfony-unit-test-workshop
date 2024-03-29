<?php

use App\Mapper\BarMapper;

test('maps the correct value', function () {
    $mapper = new BarMapper();

    expect($mapper->map('bar'))->toBe('foo');
});

test('it returns a nul l for unknown values', function () {
    $mapper = new BarMapper();

    expect($mapper->map('not-bar'))->toBeNull();
});
