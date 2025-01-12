<?php

namespace services;

use domain\User;
use repositories\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private UserRepository $userRepository;
    private string $jwtSecret;

    public function __construct(UserRepository $userRepository, string $jwtSecret)
    {
        $this->userRepository = $userRepository;
        $this->jwtSecret = $jwtSecret;
    }

    public function register(string $email, string $password, string $firstName, string $lastName): bool
    {
        $existingUser = $this->userRepository->getByEmail($email);
        if ($existingUser) {
            throw new \Exception("A user with this email already exists.");
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        return $this->userRepository->save($email, $hashedPassword, $firstName, $lastName);
    }

    public function login(string $email, string $password): string
    {
        $userData = $this->userRepository->getByEmail($email);
        if (!$userData || !password_verify($password, $userData['password'])) {
            throw new \Exception("Invalid email or password.");
        }

        $payload = [
            'id' => $userData['id'],
            'email' => $userData['email'],
            'is_superuser' => $userData['is_superuser'],
            'exp' => time() + 3600 // Token expires in 1 hour
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function authenticate(string $token): ?User
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            $userData = $this->userRepository->getById($decoded->id);
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
        } catch (\Exception $e) {
            return null;
        }
    }

    public function refresh(string $token): string
    {
        $user = $this->authenticate($token);
        if (!$user) {
            throw new \Exception("Invalid token.");
        }

        $payload = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'is_superuser' => $user->isSuperuser(),
            'exp' => time() + 3600 // Token expires in 1 hour
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
