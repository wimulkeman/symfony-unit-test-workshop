<?php

namespace tests\Unit\Mapper;

use App\Mapper\DynamicMapper;
use PHPUnit\Framework\TestCase;

class DynamicMapperTest extends TestCase
{
    public static function providedMappingValues(): array
    {
        return [
            'foo > bar' => ['foo', 'bar'],
            'bar > baz' => ['bar', 'baz'],
            'john > doe' => ['john', 'doe'],
            'doe > john' => ['doe', 'john'],
        ];
    }

    /**
     * @dataProvider providedMappingValues
     * @param string $input
     * @param string $expected
     * @return void
     */
    public function test_it_returns_the_correct_value_on_different_inputs(string $input, string $expected): void
    {
        $mapper = new DynamicMapper();

        $this->assertSame($expected, $mapper->map($input));
    }
}
