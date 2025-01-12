<?php

namespace services;

use DateTime;
use domain\ticket;
use http\Client\Curl\User;
use repositories\FlightRepository;
use repositories\TicketRepository;
use repositories\UserRepository;

class TicketService
{
    private PaymentService  $paymentService;
    private TicketRepository $ticketRepository;
    private FlightRepository $flightRepository;
    private UserRepository $userRepository;

    public function __construct(PaymentService $paymentService, TicketRepository $ticketRepository, FlightRepository $flightRepository, UserRepository $userRepository){
        $this->paymentService = $paymentService;
        $this->ticketRepository = $ticketRepository;
        $this->flightRepository = $flightRepository;
        $this->userRepository = $userRepository;
    }

    public function sellTicket($flightId, $userId, $cardId, $ticketOwnerFullName, $paymentMethod){
        $flight = $this->flightRepository->getById($flightId);
        if (!$flight){
            throw new \RuntimeException("Flight not found.");
        }

        $user = $this->userRepository->getById($userId);
        if (!$user) {
            throw new \RuntimeException("User not found.");
        }

        $transactionAmount = $flight->price;

        if($this->paymentService->withdrawMoney($transactionAmount, $userId, "BUY_TICKET", $cardId, $paymentMethod)){
            $now = new DateTime()->format('Y-m-d H:i:s');
            $this->ticketRepository->addTicket($now, $flightId, $userId, $ticketOwnerFullName, $transactionAmount);
        }
    }

    public function refundTicket(){

    }

    public function getAllTicketsByUserId($userId){
        $rawTickets = $this->ticketRepository->getTicketsByUserId($userId);

        $tickets = [];
        foreach($rawTickets as $ticket){
            $tickets[] = new Ticket(
                $ticket['id'],
                $ticket['purchasedDate'],
                $ticket['purchasedTime'],
                $ticket['ownerFullName'],
                $ticket['price'],
            );
        }

        return $tickets;
    }
}