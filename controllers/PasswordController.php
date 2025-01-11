<?php

namespace controllers;

use services\PasswordRecoveryService;

class PasswordController
{
    public PasswordRecoveryService  $passwordService;

    public function __construct(PasswordRecoveryService $passwordRecovery){
        $this->passwordService = $passwordRecovery;
    }

    public function sendResetLink($email) {
        $this->passwordService->sendResetLink($email);
    }

    public function validateCode($code) {
        return $this->passwordService->validateCode($code);
    }

    public function updatePassword($userId, $code, $newPassword) {
        $this->passwordService->updatePassword($userId, $code, $newPassword);
    }
}