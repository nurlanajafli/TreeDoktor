<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');


function loop_view($view, $data, $var = 'item', $echo = false)
{
    $out = '';
    $CI =& get_instance();
    foreach ($data as $item) {
        $out .= $CI->load->view($view, [$var => $item], true) . PHP_EOL;
    }
    if(!$echo)
        return $out;
    echo $out;
}

function str2ts($date, $format = 'Y-m-d H:i:s')
{
    if($dt = DateTime::createFromFormat($format, $date))
        return $dt->getTimestamp();
    return false;
}

function ts2str($time, $format = 'Y-m-d H:i:s'){
    try {
        $dt = new DateTime('@' . $time);
    } catch (Exception $e){
        return false;
    }
    return $dt->format($format);
}