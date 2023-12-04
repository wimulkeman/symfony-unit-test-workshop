<?php

namespace Tests\Unit\Provider;

use App\Provider\UserProvider;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Tests\Stub\Entity\User;

class UserProviderTest extends TestCase
{
    public function test_get_user_name_when_user_is_known()
    {
        $userId = 123;

        $user = new User();

        $repo = $this->createMock(UserRepository::class);

        $repo
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user)
        ;

        $provider = new UserProvider($repo);

        $this->assertSame('John Doe', $provider->getName($userId));
    }

    public function test_get_user_name_when_user_is_unknown()
    {
        $userId = 123;

        $repo = $this->createMock(UserRepository::class);

        $repo
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn(null)
        ;

        $provider = new UserProvider($repo);

        $this->assertNull($provider->getName($userId));
    }

    public function test_requesting_multiple_user_names_at_once()
    {
        $userIds = [
            123,
            456,
            789,
        ];

        $repo = $this->createMock(UserRepository::class);

        $invoker = $this->exactly(3);
        $repo
            ->expects($invoker)
            ->method('findById')
            ->willReturnCallback(function ($id) use ($invoker): ?User {
                match ($invoker->numberOfInvocations()) {
                    1 => $this->assertSame(123, $id),
                    2 => $this->assertSame(456, $id),
                    3 => $this->assertSame(789, $id),
                    default => $this->fail('Unexpected invocation'),
                };

                return match ($id) {
                    123, 789 => new User(),
                    456 => null,
                    default => $this->fail('Unexpected invocation'),
                };
            })
        ;

        $provider = new UserProvider($repo);

        $this->assertEquals(['John Doe', 'John Doe'], $provider->getNames($userIds));
    }
}
