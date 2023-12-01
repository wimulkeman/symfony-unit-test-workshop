<?php

namespace App\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PageHandler
{
    public function supports(string $type): bool
    {
        return 'page' === $type;
    }

    public function handle(string $value): Collection
    {
        $collection = new ArrayCollection([
            uniqid('foo-',true),
            'foo',
            uniqid('bar-', true),
            $value,
        ]);

        return $collection;
    }
}
