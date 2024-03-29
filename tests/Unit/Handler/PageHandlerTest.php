<?php

use App\Handler\PageHandler;

test('it supports pages', function () {
    $handler = new PageHandler();

    expect($handler->supports('page'))->toBeTrue();
});

test('it does not support other types', function () {
    $handler = new PageHandler();

    expect($handler->supports('header'))->toBeFalse();
});

test('it receives the correct amount of items', function () {
    $handler = new PageHandler();

    $handleResponse = $handler->handle('page-value');

    expect($handleResponse)->toBeInstanceOf(Countable::class);
    expect($handleResponse)->toHaveCount(4);
});

test('it contains the expected foo item', function () {
    $handler = new PageHandler();

    $handleResponse = $handler->handle('page-value');

    expect($handleResponse)->toBeIterable();
    expect($handleResponse)->toContain('foo');
});
