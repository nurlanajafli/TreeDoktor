<?php

class Migration_add_appointment_settings_to_settings_table extends CI_Migration {

    public function up() {
        $setting = [
            'stt_key_name' => 'AppointmentTaskLength',
            'stt_key_value' => '45',
            'stt_key_validate' => '',
            'stt_section' => 'Appointment',
            'stt_label' => 'Appointment task length (minutes)',
            'stt_is_hidden' => 0,
            'stt_html_attrs' => "class='select2 w-200' id='taskLength' data-href='#task-config' style='float:right'"
        ];
        $this->db->insert('settings', $setting);
    }

    public function down() {
            $this->db->where(['stt_key_name' => 'AppointmentTaskLength']);
            $this->db->delete('settings');
    }

}