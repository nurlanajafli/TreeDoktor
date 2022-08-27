<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


function additional_tax_settings()
{
    $CI =& get_instance();

    $tax_name = config_item('tax_name');
    $tax_perc = (int)config_item('tax');
    $tax_rate = ($tax_perc + 100) / 100;
    $tax_string = $tax_name . ' ' . $tax_perc . '%';

    $CI->config->set_item('tax_perc', $tax_perc);
    $CI->config->set_item('tax_rate', $tax_rate);
    $CI->config->set_item('tax_string', $tax_string);
}

function countries_select()
{
    $result = [];
    foreach (config_item('countries') as $key => $value) {
        $result[] = ['id' => $key, 'text' => $key];
    }

    return $result;
}

function autocomplete_restriction()
{
    $CI =& get_instance();
	if(config_item('countries') && !empty(config_item('countries')) && isset(config_item('countries')[config_item('office_country')])){

        $code = strtolower(config_item('countries')[config_item('office_country')]['alpha2']);
        $CI->config->set_item('autocomplete_restriction', $code);
        return $code;
    }

    $CI->config->set_item('autocomplete_restriction', null);
    return null;
}

function get_locations()
{
    $QBLocations = unserialize(config_item('QBLocations'));
    $locations = [];
    if(is_array($QBLocations) && !empty($QBLocations)) {
        foreach ($QBLocations as $loc) {
            $locations[] = ['id' => $loc['id'], 'text' => $loc['text']];
        }
    }
    return $locations;
}

function get_date_format()
{
    $result = [];
    $dates = json_decode(config_item('allDateFormat'));
    foreach ($dates as $format) {
        $result[] = ['id' => $format, 'text' => date($format)];
    }
    return $result;
}

function get_appointment_task_length()
{
    $result = [];
    $array = [15, 30, 45, 60, 90];
    foreach ($array as $item) {
        $result[] = ['id' => $item, 'text' => (string)$item];
    }

    return $result;
}

function get_task_length()
{
    $result = [];
    for ($i = 15; $i <= 60; $i += 15) {
        $result[] = ['id' => $i, 'text' => (string)$i];
    }
    return $result;
}

function get_time_format()
{
    $result = [];
    $dates = json_decode(config_item('allTimeFormats'));
    foreach ($dates as $format) {
        $result[] = ['id' => $format, 'text' => $format];
    }
    return $result;
}

function all_taxes($taxes = null)
{
    $result = [];
    if(empty($taxes))
        $taxes = json_decode(config_item('allTaxes'));

    if(is_array($taxes)){
        foreach ($taxes as $tax) {
            $text = $tax->name . ' (' . round($tax->value, 3) . '%)';
            $result[] = [
                'id' => $text,
                'text' => $text,
                'name' => $tax->name,
                'rate' => $tax->value / 100 + 1,
                'value' => $tax->value
            ];
        }
    }
    $result[] = [
        'id' => 'Tax (0%)',
        'text' => 'Tax (0%)',
        'name' => 'Tax',
        'rate' => 1,
        'value' => 0
    ];
    return $result;
}

function getDefaultTax()
{
    $result = [];
    $taxes = all_taxes();
    $defaultTaxText = config_item('taxManagement');
    foreach ($taxes as $tax) {
        $text = $tax['name'] . ' (' . round($tax['value'], 3) . '%)';
        if($defaultTaxText == $text){
            $result = [
                'name' => $tax['name'],
                'value' => $tax['value'],
                'text' => $tax['name'] . ' (' . $tax['value'] . '%)',
                'rate' => $tax['value'] / 100 + 1
            ];
            break;
        }
    }
    return $result;
}

function checkTaxInAllTaxes($tax)
{
    $result = false;
    $taxes = all_taxes();
    foreach ($taxes as $oneTax) {
        $text = $oneTax['name'] . ' (' . round($oneTax['value'], 3) . '%)';
        if($tax == $text){
            $result = [
                'name' => $oneTax['name'],
                'value' => $oneTax['value'],
                'rate' => $oneTax['value'] / 100 + 1
            ];
            break;
        }
    }
    return $result;
}

function getCurrencySymbolPositions(){
    return [
        ['id' => '{currency} {amount}', 'text' => get_currency().' 1,000.00'],
        ['id' => '{currency}{amount}', 'text' => get_currency().'1,000.00'],
        ['id' => '{amount} {currency}', 'text' => '1,000.00 '.get_currency()],
        ['id' => '{amount}{currency}', 'text' => '1,000.00'.get_currency()],
    ];
    /*return [
        ['id' => 'before', 'text' => 'Before amount'],
        ['id' => 'after', 'text' => 'After amount']
    ];*/
}

function enabled_disabled(){
    return [
        ['id' => '0', 'text' => 'Disabled'],
        ['id' => '1', 'text' => 'Enabled']
    ];
}

function scheduler_starts_dropdown() {
    $from = 1;
    $to = 10;

    for($i = $from; $i <= $to; $i++) {
        $result[] = [
            'id' => $i,
            'value' => $i,
            'text' => date(getTimeFormat(true), mktime($i, 0)),
        ];
    }
    return $result ?? [];
}

function scheduler_ends_dropdown() {
    $from = 16;
    $to = 24;

    for($i = $from; $i <= $to; $i++) {
        $result[] = [
            'id' => $i,
            'value' => $i,
            'text' => date(getTimeFormat(true), mktime($i, 0)),
        ];
    }
    return $result ?? [];
}

function debugbar_by_cookie() {
    $CI =& get_instance();
    $CI->load->helper('cookie');
    if($debugbar = get_cookie('debugbar')) {
        $CI->config->set_item('debugbar', true);
    }
}

/**
 * Update or create settings value
 *
 * @param string $keyName
 * @param string|null $keyVal
 * @param bool [$isHidden]
 * @return bool
 */
function updateSettings(string $keyName, string $keyVal = null, $isHidden = true): bool
{
    $CI =& get_instance();
    $CI->load->model('mdl_settings_orm');

    $data = [
        'stt_key_name' => $keyName,
        'stt_key_value' => $keyVal,
        'stt_is_hidden' => $isHidden
    ];
    $setting = $CI->mdl_settings_orm->get_by('stt_key_name', $keyName);

    if (is_object($setting)) {
        $result = $CI->mdl_settings_orm->update_by('stt_key_name', $keyName, $data);
    } else {
        $result = $CI->mdl_settings_orm->insert($data, true);
    }

    return $result;
}
