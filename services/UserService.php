<?php

namespace services;

use domain\User;
use repositories\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function updateUser(
        int $id,
        ?string $email = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?float $walletBalance = null,
        ?int $cardId = null
    ): bool {
        if ($email !== null) {
            $existingUser = $this->userRepository->getByEmail($email);
            if ($existingUser && $existingUser['id'] !== $id) {
                throw new \Exception("A user with this email already exists.");
            }
        }

        return $this->userRepository->update($id, $email, $firstName, $lastName, $walletBalance, $cardId);
    }

    public function getById(int $id): ?User
    {
        $userData = $this->userRepository->getById($id);
        if (!$userData) {
            return null;
        }

        return new User(
            $userData['id'],
            $userData['first_name'],
            $userData['last_name'],
            $userData['email'],
            $userData['is_superuser'],
            $userData['wallet_balance']
        );
    }
}