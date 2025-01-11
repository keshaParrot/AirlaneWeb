<?php

namespace services;

use Exception;
use repositories\UserRepository;
use repositories\VerificationCodeRepository;

class PasswordRecoveryService
{
    private VerificationCodeRepository $verificationCodeRepository;
    private UserRepository $userRepository;

    public function __construct(VerificationCodeRepository $verificationCodeRepository, UserRepository $userRepository)
    {
        $this->verificationCodeRepository = $verificationCodeRepository;
        $this->userRepository = $userRepository;
    }

    public function sendResetLink($email) {
        $user = $this->userRepository->getByEmail($email);
        if (!$user) {
            throw new Exception("User with this email was not found.");
        }

        $recoveryCode = rand(100000, 999999);
        $expiryDate = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $this->verificationCodeRepository->addVerificationCode($user['id'], $recoveryCode, $expiryDate);

        $resetLink = "https://yourfrontend.com/reset-password?code=$recoveryCode";
        mail($email, "Скидання пароля", "Перейдіть за посиланням: $resetLink");
    }

    public function validateCode($code) {
        $recovery = $this->verificationCodeRepository->findVerificationCode($code);
        if (!$recovery) {
            throw new Exception("The code is invalid or expired.");
        }
        return true;
    }

    public function updatePassword($userId, $code, $newPassword) {
        $recovery = $this->verificationCodeRepository->findVerificationCode($code);
        if (!$recovery || $recovery['User_id'] != $userId) {
            throw new Exception("Invalid code.");
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->userRepository->changePassword($userId, $hashedPassword);
        $this->verificationCodeRepository->markAsUsed($code);
    }
}