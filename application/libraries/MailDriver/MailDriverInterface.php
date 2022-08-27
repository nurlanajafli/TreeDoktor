<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Interface GpsInterface
 * @see https://code.tutsplus.com/ru/tutorials/how-to-create-custom-drivers-in-codeigniter--cms-29339
 */
interface MailDriverInterface
{
    public function send();

    public function checkIfVerifiedEmail($email);

    public function getEmail();

    public function setEmail($email);

    public function getEmailInfo();

    public function parseCallbackMessage($payload);
}
