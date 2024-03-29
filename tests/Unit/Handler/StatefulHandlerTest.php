<?php

use App\Handler\StateHandler;

beforeEach(function () {
    StateHandler::$state = 'idle';
});
test('it changes it state when a process is running', function () {
    $stateHandler = new StateHandler();
    expect($stateHandler->getState())->toBe('idle');

    $stateHandler->startProcessing();
    expect($stateHandler->getState())->toBe('processing');

    $stateHandler->keepRunning();
    expect($stateHandler->getState())->toBe('pending');
});
test('it resets its internal state', function () {
    $stateHandler = new StateHandler();
    expect($stateHandler->getState())->toBe('idle');
});
test('it throws an exception when steps are skipped', function () {
    $this->expectException(LogicException::class);

    $stateHandler = new StateHandler();
    $stateHandler->stopRunning();
});
