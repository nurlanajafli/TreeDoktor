<?php

class Migration_add_sync_setting_to_settings_table extends CI_Migration
{

    public function up()
    {
        $setting = [
            'stt_key_name' => 'synchronization',
            'stt_key_value' => json_encode([
                "in" => [
                    "textOn" => "Enable sync in QuickBooks",
                    "textOff" => "Disable sync in QuickBooks",
                    "state" => 1
                ],
                "from" => [
                    "textOn" => "Enable sync from QuickBooks",
                    "textOff" => "Disable sync from QuickBooks",
                    "state" => 1
                ]
            ]),
            'stt_key_validate' => '',
            'stt_section' => 'QuickBooks',
            'stt_label' => 'Synchronization control',
            'stt_is_hidden' => 0
        ];
        $this->db->insert('settings', $setting);
    }

    public function down()
    {
        $this->db->where(['stt_key_name' => 'synchronization']);
        $this->db->delete('settings');
    }

}