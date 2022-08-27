<?php

function toLog(&$log, $data){
    $date = DateTime::createFromFormat('U.u', microtime(TRUE));
    $log[$date->format('Y-m-d H:i:s.u')] = $data;
}