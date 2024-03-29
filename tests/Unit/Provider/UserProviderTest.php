<?php

use App\Provider\UserProvider;
use App\Repository\UserRepository;
use Tests\Stub\Entity\User;


test('get user name when user is known', function () {
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

    expect($provider->getName($userId))->toBe('John Doe');
});

test('get user name when user is unknown', function () {
    $userId = 123;

    $repo = $this->createMock(UserRepository::class);

    $repo
        ->expects($this->once())
        ->method('findById')
        ->with($userId)
        ->willReturn(null)
    ;

    $provider = new UserProvider($repo);

    expect($provider->getName($userId))->toBeNull();
});

test('requesting multiple user names at once', function () {
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
                1 => expect($id)->toBe(123),
                2 => expect($id)->toBe(456),
                3 => expect($id)->toBe(789),
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

    expect($provider->getNames($userIds))->toEqual(['John Doe', 'John Doe']);
});
