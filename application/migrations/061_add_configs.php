<?php

class Migration_add_configs extends CI_Migration {
    public $insert = [
        [
            'stt_key_name'=>'office_address',
            'stt_key_value'=>'',
            'stt_key_validate'=>'',
            'stt_section'=>'Office Information',
            'stt_label' => 'Office Address'
        ],
        [
            'stt_key_name'=>'office_region',
            'stt_key_value'=>'',
            'stt_key_validate'=>'',
            'stt_section'=>'Office Information',
            'stt_label' => 'Office Region'
        ],
        [
            'stt_key_name'=>'office_city',
            'stt_key_value'=>'',
            'stt_key_validate'=>'',
            'stt_section'=>'Office Information',
            'stt_label' => 'Office City'
        ],
        [
            'stt_key_name'=>'office_state',
            'stt_key_value'=>'',
            'stt_key_validate'=>'',
            'stt_section'=>'Office Information',
            'stt_label' => 'Office State'
        ],
        [
            'stt_key_name'=>'office_zip',
            'stt_key_value'=>'',
            'stt_key_validate'=>'',
            'stt_section'=>'Office Information',
            'stt_label' => 'Office Zip'
        ],
        [
            'stt_key_name'=>'office_country',
            'stt_key_value'=>'',
            'stt_key_validate'=>'',
            'stt_section'=>'Office Information',
            'stt_label' => 'Office Country'
        ],
        [
            'stt_key_name'=>'office_description',
            'stt_key_value'=>'',
            'stt_key_validate'=>'',
            'stt_section'=>'Office Information',
            'stt_label' => 'Office Description'
        ]
    ];
    public function up() {
        
        foreach ($this->insert as $key => $value) {
            $this->db->insert('settings', $value);   
        }

    }

    public function down() {
        foreach ($this->insert as $key => $value) {
            $this->db->where(['stt_key_name' => $value['stt_key_name']]);
            $this->db->delete('settings');
        }
    }

}
