<?php

declare(strict_types=1);

namespace Tests\Stub\Entity;

use App\Entity\User as BaseUser;

class User extends BaseUser
{
    public function getName(): string
    {
        return 'John Doe';
    }
}