<?php

use App\Mapper\DynamicMapper;

dataset('providedMappingValues', function () {
    return [
        'foo > bar' => ['foo', 'bar'],
        'bar > baz' => ['bar', 'baz'],
        'john > doe' => ['john', 'doe'],
        'doe > john' => ['doe', 'john'],
    ];
});

test('it returns the correct value on different inputs', function (string $input, string $expected) {
    $mapper = new DynamicMapper();

    expect($mapper->map($input))->toBe($expected);
})->with('providedMappingValues');
