<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class SettingsPortal extends Portal_Controller
{
    public function __construct()
    {
        parent::__construct(true);
    }

    public function get_settings()
    {
        $this->response([
            'signature' => $this->config->item('portal_signature') ? $this->config->item('portal_signature') : ($this->config->item('processing') ? false : true),
            'processing' => $this->config->item('processing') ?? false,
            'display_tax_in_estimate' => config_item('display_tax_in_estimate'),
            'percents_estimate_deposit' => config_item('percents_estimate_deposit'),
            'currency' => [
                'currency_symbol' => config_item('currency_symbol'),
                'currency_template' => config_item('currency_symbol_position') ?: '{currency} {amount}',
                'currency_digit_separator' => config_item('currency_digit_separator')??',',
            ],
            'companyInfo' => [
                'logo' => $this->config->item('payment_logo_name') ?? true,
                'name' => $this->config->item('company_name_long') ?? true,
                'address' => [
                    'street' => $this->config->item('office_address') ?? '',
                    'city' => $this->config->item('office_city') ?? '',
                    'region' => $this->config->item('office_region') ?? '',
                    'state' => $this->config->item('office_state') ?? '',
                    'office_country' => $this->config->item('office_region') ?? '',
                ],
                'email'  => config_item('account_email_address'),
                'phone' => config_item('office_phone') ?? '',
                'office_phone_mask' => config_item('office_phone_mask') ?? '',

            ],
        ], 200);
    }
}
