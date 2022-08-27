<?php 
function get_brand_id($estimate, $client){
    $client_brand_id = element('client_brand_id', (array)$client, default_brand());
    $estimate_brand_id = element('estimate_brand_id', (array)$estimate, 0);
    return ($estimate_brand_id)?$estimate_brand_id:$client_brand_id;
}

function default_brand() {
    $brands = config_item('brands')?:[];
    foreach ($brands as $brand) {
        if ($brand->b_is_default)
            return $brand->b_id;
    }

    return 0;
}

function brand_office_lat($brand_id){
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->b_company_lat)){
        return $brands[$brand_id]->b_company_lat;
    }
    return config_item('office_lat');
}

function brand_office_lon($brand_id){
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->b_company_lng)){
        return $brands[$brand_id]->b_company_lng;
    }
    return config_item('office_lon');
}

function brand_office_address($brand_id){
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->b_company_address)){
        return $brands[$brand_id]->b_company_address;
    }
    return config_item('office_address');
}

function brand_office_region($brand_id){
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->b_company_region)){
        return $brands[$brand_id]->b_company_region;
    }
    return config_item('office_region');
}

function brand_office_city($brand_id){
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->b_company_city)){
        return $brands[$brand_id]->b_company_city;
    }
    return config_item('office_city');
}

function brand_office_state($brand_id){
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->b_company_state)){
        return $brands[$brand_id]->b_company_state;
    }
    return config_item('office_state');
}

function brand_office_zip($brand_id){
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->b_company_zip)){
        return $brands[$brand_id]->b_company_zip;
    }
    return config_item('office_zip');
}

function brand_office_country($brand_id){
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->b_company_country)){
        return $brands[$brand_id]->b_company_country;
    }
    return config_item('office_country');
}

function get_brand_logo($brand_id, $logo_key, $default){
    $brands = config_item('brands');
    if(!$brands || !isset($brands[$brand_id]->images[$logo_key]))
        return $default;

    return $brands[$brand_id]->images[$logo_key]['url'];
}

function brand_phone($brand_id, $clean=false)
{
    $brands = config_item('brands');
    if($clean){
        if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->bc_phone_clean)){
            return $brands[$brand_id]->bc_phone_clean;
        }

        return config_item('office_phone');
    }

    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->bc_phone)){
        return numberTo($brands[$brand_id]->bc_phone);
    }

    return config_item('office_phone_mask');
}

function brand_name($brand_id, $long=false)
{
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && $brands[$brand_id]->b_name){
        return $brands[$brand_id]->b_name;
    }

    return (!$long)?config_item('company_name_short'):config_item('company_name_long');
}

function brand_email($brand_id)
{
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->bc_email)){
        return $brands[$brand_id]->bc_email;
    }

    return config_item('account_email_address');
}

function brand_site($brand_id) {
    $brands = config_item('brands');
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->bc_site)){
        return $brands[$brand_id]->bc_site;
    }

    return config_item('company_site');
}

function brand_site_http($brand_id) {
    $brands = config_item('brands');
    $site = false;
    if($brands && isset($brands[$brand_id]) && isset($brands[$brand_id]->bc_site)){
        $site = $brands[$brand_id]->bc_site;
    }

    if(!$site)
        return config_item('company_site_http');

    $site = str_replace('https://', '', str_replace('http://', '', $site));
    return 'http://'.$site;
}

if(!function_exists('brand_address')){
    function brand_address($brand_id, $default=null, $short=false){

        $brands = config_item('brands');
        if(!$brands || !isset($brands[$brand_id])){
            return $default;
        }
        if($short) {
            return element('b_company_address', (array)$brands[$brand_id], FALSE);
        }
        $result_array = [
            element('b_company_address', (array)$brands[$brand_id], FALSE), 
            element('b_company_city', (array)$brands[$brand_id], FALSE),
            element('b_company_state', (array)$brands[$brand_id], FALSE),
            element('b_company_zip', (array)$brands[$brand_id], FALSE),
            element('b_company_country', (array)$brands[$brand_id], FALSE) 
        ];

        return implode(', ', array_filter($result_array));
    }
}

function get_estimate_terms($brand_id) {
    $brands = config_item('brands');

    $result = false;
    if($brands && isset($brands[$brand_id])){

        
        $result = trim(strip_tags(htmlspecialchars_decode(html_entity_decode($brands[$brand_id]->b_estimate_terms, ENT_COMPAT, 'UTF-8')), ['div', 'input', 'font', 'ul', 'li', 'p', 'a', 'span', 'label', 'strong', 'b', 'i', 'u', 'img', 'br', 'pagebreak']));
    }
   
    return $result;
}

function get_invoice_terms($brand_id){
    $brands = config_item('brands');

    $result = false;
    if($brands && isset($brands[$brand_id])){
        $result = trim(strip_tags(htmlspecialchars_decode(html_entity_decode($brands[$brand_id]->b_payment_terms, ENT_COMPAT, 'UTF-8')), ['div', 'input', 'font', 'ul', 'li', 'p', 'a', 'span', 'label', 'strong', 'b', 'i', 'u', 'img', 'br', 'pagebreak']));
    }
   
    return $result;
}

function get_pdf_footer($brand_id){
    $brands = config_item('brands');

    $result = false;
    if($brands && isset($brands[$brand_id])){
        $result = trim(strip_tags(htmlspecialchars_decode(html_entity_decode($brands[$brand_id]->b_pdf_footer, ENT_COMPAT, 'UTF-8')), ['div', 'input', 'font', 'ul', 'li', 'p', 'a', 'span', 'label', 'strong', 'b', 'i', 'u', 'img', 'br', 'pagebreak']));
    }
   
    return $result;
}

function brand_team_signature($brand_id){
    return config_item('default_email_from_second');
}

function item_branding($brand_id, $text){
    if(!$brand_id)
        return $text;

    $text = str_replace(
        ['[COMPANY_NAME]', '[COMPANY_EMAIL]', '[COMPANY_PHONE]', '[COMPANY_ADDRESS]', '[COMPANY_BILLING_NAME]', '[COMPANY_WEBSITE]'],
        [
            brand_name($brand_id),
            brand_email($brand_id),
            brand_phone($brand_id),
            brand_address($brand_id, config_item('office_address') . ', ' . config_item('office_city') . ', ' . config_item('office_zip')),
            brand_name($brand_id, true),
            brand_site($brand_id)
        ],
        $text
    );
    return $text;
}

function getReviewLinksForSelect2($links){
    $result = [];
    foreach ($links as $link){
        $result[] = [
            'id' => $link['brl_link'],
            'text' => $link['brl_name']
        ];
    }
    return $result;
}
