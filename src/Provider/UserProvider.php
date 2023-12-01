<?php

namespace App\Provider;

use App\Repository\UserRepository;

class UserProvider
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getName(int $userId): ?string
    {
        return $this->userRepository->findById($userId)?->getName();
    }

    /**
     * @param array<int, int> $userIds
     *
     * @return array<int, string>
     */
    public function getNames(array $userIds): array
    {
        $userNames = [];
        foreach ($userIds as $id) {
            $userNames[] = $this->getName($id);
        }

        return array_values(array_filter($userNames));
    }
}
