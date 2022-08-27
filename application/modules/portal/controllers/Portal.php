<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Portal extends Portal_Controller
{
    function index() {
        $this->load->view('index');
    }
}