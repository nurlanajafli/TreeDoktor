<?php

defined('BASEPATH') or exit('No direct script access allowed');

interface MessagesInterface
{
    public function callback($post);

    public function send($number, $message, $from);
}