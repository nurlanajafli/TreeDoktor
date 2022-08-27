<?php

class Migration_add_tax_settings_to_settings_table extends CI_Migration {

    public function up() {
        $settings[] = [
            'stt_key_name' => 'allTaxes',
            'stt_key_value' => json_encode([['name' => config_item('tax_name'), 'value' => config_item('tax_perc')]]),
            'stt_key_validate' => '',
            'stt_section' => 'Prices',
            'stt_label' => '',
            'stt_is_hidden' => 1
        ];
        $settings[] = [
            'stt_key_name' => 'taxManagement',
            'stt_key_value' => config_item('tax_name') . ' (' . config_item('tax_perc') . '%)',
            'stt_key_validate' => '',
            'stt_section' => 'Prices',
            'stt_label' => 'Tax',
            'stt_is_hidden' => 0,
            'stt_html_attrs' => 'class="select2 w-70" data-href="#allTaxes" id="tax"'
        ];
        foreach ($settings as $setting)
            $this->db->insert('settings', $setting);
    }

    public function down() {
        $settings = [
            'allTaxes', 'taxManagement'
        ];
        foreach ($settings as $setting) {
            $this->db->where(['stt_key_name' => $setting]);
            $this->db->delete('settings');
        }
    }

}