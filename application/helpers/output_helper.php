<?php

function json_out($data, $httpCode = 200)
{
    $CI =& get_instance();
    $CI->output
        ->set_content_type('application/json')
        ->set_status_header($httpCode)
        ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    return;
}
