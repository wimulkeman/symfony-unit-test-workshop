<?php

namespace Tests\Mapper;

use App\Mapper\BarMapper;
use PHPUnit\Framework\TestCase;

class BarMapperTest extends TestCase
{
    public function test_maps_the_correct_value()
    {
        $mapper = new BarMapper();

        $this->assertSame('foo', $mapper->map('bar'));
    }

    public function test_it_returns_a_nulL_for_unknown_values()
    {
        $mapper = new BarMapper();

        $this->assertNull($mapper->map('not-bar'));
    }
}
