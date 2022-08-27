<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Interface PaymentInterface
 * @see https://code.tutsplus.com/ru/tutorials/how-to-create-custom-drivers-in-codeigniter--cms-29339
 */
interface PaymentInterface
{
    public function init();

    public function tokenize($number, $expr_month, $expr_year, $cvd);

    public function createProfile(array $billingData, $token, string $cardholderName, $additional);

    public function profileCards(string $profileId);

    public function getCard(string $profileId, int $cardId);

    public function profileAddCard(string $profileId, $billingData, $token, $cardholderName, $additional);

    public function profileDeleteCard(string $profileId, int $cardId);

    public function cardType(string $inputType);

    public function pay(array $data, float $amount, string $order_number);

    public function refund(int $transaction_id, float $amount);

    public function void(int $transaction_id);

    public function getTransaction(int $transaction_id);

    public function checkTransaction(int $transaction_id);

    public function getForm(array $data, string $dir);

    public function getProfile(string $profileId);

    public function deleteProfile(string $profileId);
}