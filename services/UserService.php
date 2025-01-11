<?php

namespace services;

use domain\User;
use repositories\UserRepository;
use repositories\VerificationCodeRepository;

class UserService
{
    private UserRepository $userRepository;
    private VerificationCodeRepository $verificationCodeRepository;

    public function __construct(UserRepository $userRepository, VerificationCodeRepository $verificationCodeRepository)
    {
        $this->userRepository = $userRepository;
        $this->verificationCodeRepository = $verificationCodeRepository;
    }

    public function save(string $email, string $password, string $firstName, string $lastName): bool
    {
        $existingUser = $this->userRepository->getByEmail($email);
        if ($existingUser) {
            throw new \Exception("A user with this email already exists.");
        }

        return $this->userRepository->save($email, $password, $firstName, $lastName);
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

    public function login(string $email, string $password): ?User
    {
        $userData = $this->userRepository->getByEmail($email);
        if (!$userData || !password_verify($password, $userData['password'])) {
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