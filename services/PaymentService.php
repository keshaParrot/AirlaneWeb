<?php

namespace services;

use DateTime;
use repositories\CardRepository;
use repositories\TransactionRepository;
use repositories\UserRepository;

class PaymentService
{
    private UserRepository $userRepository;
    private TransactionRepository $transactionRepository;
    private CardRepository $cardRepository;

    public function __construct(UserRepository $userRepository, TransactionRepository $transactionRepository, CardRepository $cardRepository)
    {
        $this->userRepository = $userRepository;
        $this->transactionRepository = $transactionRepository;
        $this->cardRepository = $cardRepository;
    }

    //api
    public function GetAllUserCard($userId): array
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            throw new \RuntimeException("User not found.");
        }

        return $this->cardRepository->getAllByUserId($userId);
    }

    public function withdrawMoney($amount,
                                  $userId,
                                  $transactionType,
                                  $cardId,
                                  $paymentMethod): bool {

        $user = $this->userRepository->getById($userId);
        if (!$user) {
            throw new \RuntimeException("User not found.");
        }

        if ($paymentMethod === 'Wallet' && $user['wallet_balance'] < $amount) {
            throw new \RuntimeException("Insufficient funds in wallet.");
        }

        elseif ($paymentMethod === 'debit' && !$this->validateUserCard($userId, $cardId)) {
            throw new \RuntimeException("card does not exist or you dont have a money.");
        }

        return $this->addTransaction('withdraw',$amount, $userId, $transactionType, $cardId, $paymentMethod);
    }

    //api
    public function depositMoneyFromDebit($amount,
                                 $userId,
                                 $cardId): bool
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            throw new \RuntimeException("User not found.");
        }

        if (!$this->validateUserCard($userId,$cardId)) {
            throw new \RuntimeException("card does not exist or you dont have a money.");
        }

        return $this->addTransaction('deposit',$amount, $userId, "DEPOSIT", $cardId, "debit");
    }
    public function depositMoneyFromSystem($amount,
                                 $userId,
                                 $transactionType): bool
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            throw new \RuntimeException("User not found.");
        }

        return $this->addTransaction('deposit',$userId, $amount, $transactionType, null ,"system");
    }

    //api
    public function assignCardToUser($userId, $cardNumber, $expiryDate)
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            throw new \RuntimeException("User not found.");
        }
        if(!$this->validateCardNumber($cardNumber)){
            throw new \RuntimeException("card number is invalid.");
        }
        $cardId = $this->cardRepository->addCard($cardNumber, $expiryDate);
        $this->userRepository->addCardToUser($userId, $cardId);
    }

    //api
    public function removeCardFromUser($userId, $cardId)
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            throw new \RuntimeException("User not found.");
        }

        $card = $this->cardRepository->getByCardId($cardId);
        if (!$card) {
            throw new \RuntimeException("card for provided user not found.");
        }

        return $this->cardRepository->deleteCardById($cardId);
    }

    private function validateUserCard($userId, $cardId) : bool
    {
        $card = $this->cardRepository->getByCardId($cardId);
        if (!$card) {
            throw new \RuntimeException("card for provided user not found.");
        }

        if (!$this->validateCardNumber($cardId)){
            throw new \RuntimeException("card not valid.");
        }
        return true;
    }
    private function validateCardNumber($cardNumber) : bool
    {
        //do validate
        return true;
    }
    private function addTransaction(
        $action,
        $amount,
        $userId,
        $transactionType,
        $cardId,
        $paymentMethod
    ): bool {
        error_log($userId);
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Amount must be greater than zero.");
        }
        if($action === 'withdraw' ) {
            if ($paymentMethod === 'Wallet'){
                $this->userRepository->subtractMoneyFromWallet($userId, $amount);
            }
            else if ($paymentMethod === 'Debit') {
                //take money from debit card
            }
            else{
                throw new \RuntimeException("Invalid payment method.");
            }
        }
        if ($action === 'deposit' ) {
            if ($paymentMethod === 'system'){
                $this->userRepository->addMoneyToWallet($userId, $amount);
            }
            else if ($paymentMethod === 'debit'){
                //here will be logic of validate bank card is amount good, and if yes pay money
                $this->userRepository->addMoneyToWallet($userId, $amount);
            }
        }

        $dateTime = new DateTime();
        $currentDateTime = $dateTime->format('Y-m-d H:i:s');

        return $this->transactionRepository->addTransaction(
            $amount,
            $userId,
            $transactionType,
            $currentDateTime,
            $cardId,
            $paymentMethod
        );
    }
}