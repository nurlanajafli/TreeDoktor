<?php

class Migration_add_payment_settings_to_settings_table extends CI_Migration {

    public function up() {
        $settings[] = [
            'stt_key_name' => 'payment_bambora',
            'stt_key_value' => '{
                                    "merchantID": "300207718",
                                    "apiKeys": {
                                        "payments": "4a697Ebd9939415d9e3F0576ceB8D2CB",
                                        "reporting": "baA85C9071B3462aAe0F5d452c024C5F",
                                        "profiles": "2D4E5A0C3E0A4AD98CD44215BCB47D24"
                                    },
                                    "apiVersion": "v1",
                                    "platform": "api"
                                }',
                'stt_key_validate' => null,
            'stt_section' => 'Payment Gateway',
            'stt_label' => 'Bambora.com',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'payment_authorize',
            'stt_key_value' => '{
                                    "loginId": "6P2b5pPT",
                                    "transactionKey": "226hc52ghND23VKK",
                                    "publicKey": "Simon",
                                    "isChase": true,
                                    "isSandbox": true,
                                    "isValidationEnabled": true
                                }',
            'stt_key_validate' => null,
            'stt_section' => 'Payment Gateway',
            'stt_label' => 'Authorize.net',
            'stt_is_hidden' => 1,
        ];
        foreach ($settings as $setting)
            $this->db->insert('settings', $setting);
    }

    public function down() {
        $settings = [
            'payment_bambora', 'payment_authorize'
        ];
        foreach ($settings as $setting) {
            $this->db->where(['stt_key_name' => $setting]);
            $this->db->delete('settings');
        }
    }

}