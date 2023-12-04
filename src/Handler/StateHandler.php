<?php

namespace App\Handler;

use LogicException;

class StateHandler
{
    public static string $state = 'idle';

    public function startProcessing(): void
    {
        if ('idle' !== self::$state) {
            throw new LogicException('Invalid state');
        }

        self::$state = 'processing';
    }

    public function keepRunning(): void
    {
        if (!in_array(self::$state, ['processing', 'pending'], true)) {
            throw new LogicException('Invalid state');
        }

        self::$state = 'pending';
    }

    public function stopRunning(): void
    {
        if (!in_array(self::$state, ['processing', 'pending'], true)) {
            throw new LogicException('Invalid state');
        }

        self::$state = 'idle';
    }

    public function getState(): string
    {
        return self::$state;
    }
}
