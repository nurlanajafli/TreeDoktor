<?php


class MY_Driver extends CI_Driver
{

    public function __construct()
    {
        spl_autoload_register(function($name){
            if(file_exists(APPPATH . $name.'.php'))
                require_once APPPATH . $name.'.php';
        });

    }
}