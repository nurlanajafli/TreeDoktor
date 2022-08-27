<?php

class Migration_add_sync_invoice_numbers_settings_to_settings_table extends CI_Migration {

    public function up() {
        $setting = [
            'stt_key_name' => 'syncInvoiceNO',
            'stt_key_value' => 'Disable',
            'stt_key_validate' => '',
            'stt_section' => 'QuickBooks',
            'stt_label' => 'Sync numbering QuickBooks Invoice',
            'stt_is_hidden' => 0,
            'stt_html_attrs' => 'type="button" class="btn btn-danger syncInvoiceNO"'
        ];
        $this->db->insert('settings', $setting);
    }

    public function down() {
        $this->db->where(['stt_key_name' => 'syncInvoiceNO']);
        $this->db->delete('settings');
    }

}