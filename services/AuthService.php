<?php

namespace services;

use DateTime;
use domain\User;
use repositories\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use repositories\VerificationCodeRepository;

class AuthService
{
    private UserRepository $userRepository;
    private VerificationCodeRepository $verificationCodeRepository;
    private string $jwtSecret;

    public function __construct(UserRepository $userRepository, VerificationCodeRepository $verificationCodeRepository, string $jwtSecret)
    {
        $this->userRepository = $userRepository;
        $this->verificationCodeRepository = $verificationCodeRepository;
        $this->jwtSecret = $jwtSecret;
    }

    public function register(string $email, string $password, string $firstName, string $lastName): bool
    {
        $existingUser = $this->userRepository->getByEmail($email);
        if ($existingUser) {
            throw new \Exception("A user with this email already exists.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email format.");
        }
        if (strlen($password) < 6) {
            throw new \Exception("Password must be at least 8 characters.");
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $id = $this->userRepository->save($email, $hashedPassword, $firstName, $lastName);
        $code = $this->createValidateMailCode($id);
        if ($code) {
            return true;
        }
        return false;
    }

    private function createValidateMailCode($id): bool{
        $recoveryCode = rand(100000, 999999);
        $expiryDate = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        return $this->verificationCodeRepository->addVerificationCode($id, $recoveryCode, $expiryDate);
    }

    public function validateMail($email, $code): bool{
        $codeInstance = $this->verificationCodeRepository->findVerificationCode($code);
        $userInstance = $this->userRepository->getByEmail($email);
        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');
        if (!$codeInstance) {
            throw new \Exception("Code does not exist.");
        }
        if (!$userInstance){
            throw new \Exception("User mail not match try to go on link in mail.");
        }
        if ($codeInstance < $now){
            throw new \Exception("Code expired, code will resent on mail.");
        }
        if ($codeInstance['user_Id'] != $userInstance['id']){
            throw new \Exception("User mail not match try to go on link in mail.");
        }
        $this->userRepository->update($userInstance['id']);
        return true;
    }

    public function login(string $email, string $password): string
    {
        $userData = $this->userRepository->getByEmail($email);
        if (!$userData || !password_verify($password, $userData['password'])) {
            throw new \Exception("Invalid email or password.");
        }

        if($userData['is_active'] != 1){
            throw new \Exception("User account is not verified.");
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
