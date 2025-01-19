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

        $transactionAmount = $flight->getPrice();

        if($this->paymentService->withdrawMoney($transactionAmount, $userId, "BUY_TICKET", $cardId, $paymentMethod)){
            $date = new DateTime();
            $now = $date->format('Y-m-d H:i:s');
            $this->ticketRepository->addTicket($now, $flightId, $userId, $ticketOwnerFullName, $transactionAmount);
        }
    }

    public function refundTicket($userId, $ticketId){
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            throw new \RuntimeException("User not found.");
        }

        $ticket = $this->ticketRepository->getTicketById($ticketId);
        if ($ticket){
            if ($this->isTicketCanBeRefunded($userId)){
                $transactionAmount = $ticket->price;

                if($this->paymentService->depositMoneyFromSystem($transactionAmount, $userId, "REFUND_TICKET")){
                    $this->ticketRepository->deleteTicketById($ticketId);
                }
            }
        }else{
            throw new \RuntimeException("ticket not found.");
        }
    }

    private function isTicketCanBeRefunded($ticket): bool{
        $flight = $this->flightRepository->getById($ticket->flightId);
        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');

        if($now > $flight->arrivalDate->modify('+2 hours')){
            return false;
        }
        if($now > $ticket->purchaseDate->modify('+6 hours')){
            return false;
        }

        return true;
    }

    public function getAllTicketsByUserId($userId){
        $rawTickets = $this->ticketRepository->getTicketsByUserId($userId);

        $tickets = [];
        foreach($rawTickets as $ticket){
            $flight = $this->flightRepository->getById($ticket['Flight_id']);
            $tickets[] = new Ticket(
                $ticket['id'],
                $ticket['purchased_date'],
                $flight->getDepartureDateTime(),
                $ticket['user_name'],
                $ticket['price'],
            );
        }

        return $tickets;
    }
}