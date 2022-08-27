<?php

class Migration_add_quickbooks_settings_to_settings_table extends CI_Migration {

    public function up()
    {
        $settings[] = [
            'stt_key_name' => 'clientID',
            'stt_key_value' => '',
            'stt_key_validate' => '',
            'stt_section' => 'QuickBooks',
            'stt_label' => 'Client ID',
            'stt_is_hidden' => 0
        ];
        $settings[] = [
            'stt_key_name' => 'clientSecret',
            'stt_key_value' => '',
            'stt_key_validate' => '',
            'stt_section' => 'QuickBooks',
            'stt_label' => 'Client Secret',
            'stt_is_hidden' => 0
        ];
        $settings[] = [
            'stt_key_name' => 'accessTokenKey',
            'stt_key_value' => '',
            'stt_key_validate' => '',
            'stt_section' => '',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'refreshTokenKey',
            'stt_key_value' => '',
            'stt_key_validate' => '',
            'stt_section' => '',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'QBORealmID',
            'stt_key_value' => '',
            'stt_key_validate' => '',
            'stt_section' => '',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'baseUrl',
            'stt_key_value' => 'production',
            'stt_key_validate' => '',
            'stt_section' => '',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'accessToken',
            'stt_key_value' => '',
            'stt_key_validate' => '',
            'stt_section' => '',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'AuthorizationRequestUrl',
            'stt_key_value' => 'https://appcenter.intuit.com/connect/oauth2',
            'stt_key_validate' => '',
            'stt_section' => '',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'TokenEndPointUrl',
            'stt_key_value' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            'stt_key_validate' => '',
            'stt_section' => '',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'OauthScope',
            'stt_key_value' => 'com.intuit.quickbooks.accounting openid profile email phone address',
            'stt_key_validate' => '',
            'stt_section' => '',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'OauthRedirectUri',
            'stt_key_value' => '',
            'stt_key_validate' => '',
            'stt_section' => 'QuickBooks',
            'stt_label' => 'Oauth Redirect Uri',
            'stt_is_hidden' => 0
        ];


        foreach ($settings as $setting)
            $this->db->insert('settings', $setting);
    }

    public function down()
    {
        $settings = [
            'OauthRedirectUri', 'OauthScope', 'TokenEndPointUrl', 'AuthorizationRequestUrl', 'accessToken', 'baseUrl', 'QBORealmID', 'refreshTokenKey', 'accessTokenKey', 'clientSecret', 'clientID'
        ];
        foreach ($settings as $setting) {
            $this->db->where(['stt_key_name' => $setting]);
            $this->db->delete('settings');
        }
    }

}