<?php

class Migration_add_html_settings_attrs extends CI_Migration {

    public function up() {
        $this->db->update('settings', ['stt_html_attrs'=>'class="select2 w-100" data-href="#countries-config"'], ['stt_key_name'=>'office_country']);
    }

    public function down() {
        $this->db->update('settings', ['stt_html_attrs'=>''], ['stt_key_name'=>'office_country']);
    }

}