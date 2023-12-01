<?php

namespace App\Mapper;

class BarMapper
{
    public function map(string $bar): ?string
    {
        if ('bar' !== $bar) {
            return null;
        }

        return 'foo';
    }
}
