<?php

/**
 * Client helper
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

    /**
     * Get all taxes with client tax for US if not exist in system all taxes
     *
     * @param $client
     * @return array
     */
	function get_all_taxes_with_client_tax($client = null) {
        $allTaxes = all_taxes();
        $allTaxes[] = [
            'id' => 'System default',
            'text' => 'System default',
            'name' => null,
            'rate' => null,
            'value' => null
        ];

        if ($client) {
            if (config_item('office_country') === 'United States of America') {
                if (!empty($client->client_tax_name)) {
                    $text = $client->client_tax_name . ' (' . round($client->client_tax_value, 3) . '%)';
                    $checkTax = checkTaxInAllTaxes($text);
                    if (!$checkTax) {
                        $allTaxes[] = [
                            'id' => $text,
                            'text' => $text,
                            'name' => $client->client_tax_name,
                            'rate' => $client->client_tax_rate,
                            'value' => round($client->client_tax_value, 3)
                        ];
                    }
                }
            }
        }

		return $allTaxes;
	}

    /**
     * Get client tax text
     *
     * @param $client
     * @return array
     */
	function get_client_tax_text($client) {
	    $result = [
	        'editText' => 'System default',
            'taxText' => config_item('taxManagement')
        ];

	    if ($client && !empty($client->client_tax_name)) {
	        $clientTax = $client->client_tax_name . ' (' . round($client->client_tax_value, 3) . '%)';
	        $result = [
                'editText' => $clientTax,
                'taxText' => $clientTax
            ];
        }

	    return $result;
    }
