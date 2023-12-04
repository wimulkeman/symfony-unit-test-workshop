<?php

declare(strict_types=1);

namespace App\Mapper;

use InvalidArgumentException;

final class DynamicMapper
{
    public function map(string $input): string
    {
        return match ($input) {
            'foo' => 'bar',
            'bar' => 'baz',
            'john' => 'doe',
            'doe' => 'john',
            default => throw new InvalidArgumentException('Invalid input'),
        };
    }
}