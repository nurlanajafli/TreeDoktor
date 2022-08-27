<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function collectedBonuses($employee_id, $payroll_id, $onlyThisPayroll = FALSE)
{
    $CI = &get_instance();
    $CI->load->model('mdl_schedule');
    $CI->load->model('mdl_payroll');

    $payroll = $CI->mdl_payroll->get($payroll_id);
    if (!$onlyThisPayroll)
        return $CI->mdl_schedule->get_collected_bonuses_sum1($employee_id, $payroll->payroll_end_date);
    return $CI->mdl_schedule->get_collected_bonuses_sum1($employee_id, $payroll->payroll_end_date, $payroll->payroll_start_date);
}


if (!function_exists("getColorAndIcon")) {
    function getColorAndIcon($lat, $lon, $office, $app, $in_office, $app_in_office)
    {
        $isLink = false;
        if ($office || $app) {
            if ($office) {
                $icon ='desktop';
                $color = $in_office ? '#8ec165' : '#ffc333';
            } else {
                $icon ='mobile fa-2x';
                $color = $app_in_office ? '#8ec165' : '#ffc333';
            }
            if ($lat && $lat != 'false' && $lon && $lon != 'false') {
                $isLink = true;
            } else {
                $color = '#bebebe';
            }
        } else {
            $icon = 'pencil-square-o';
            $color = '#bebebe';
        }

        return [
            'icon' => $icon,
            'color' => $color,
            'isLink' => $isLink,
        ];
    }
}

//end of file auth_helper.php
