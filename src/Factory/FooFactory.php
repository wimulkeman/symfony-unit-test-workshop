<?php

declare(strict_types=1);

namespace App\Factory;

use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\ModelFactory;

final class FooFactory extends Factory
{

    /**
     * @inheritDoc
     */
    protected static function getClass(): string
    {
        // TODO: Implement getClass() method.
    }

    /**
     * @inheritDoc
     */
    protected function getDefaults(): array
    {
        return [
            'name' => parent::faker()->name;
        ];
    }
}