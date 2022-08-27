<?php

class Migration_add_global_variable extends CI_Migration {

    public function up() {
        $this->db->where(['stt_key_name' => "tax_rate"]);
        $this->db->delete('settings');
        /*---------------------------------------------*/
        $data = [
            [
                'stt_key_name'=>'tax',
                'stt_key_value'=>'0',
                'stt_key_validate'=>'numeric',
                'stt_section'=>'Prices',
                'stt_label'=>'Tax %'
            ],
            [
                'stt_key_name'=>'tax_name',
                'stt_key_value'=>'Tax',
                'stt_key_validate'=>'max_length[50]',
                'stt_section'=>'Prices',
                'stt_label'=>'Tax Name'
            ]
        ];


        foreach ($data as $key => $row) {
            $this->db->insert('settings', $row);    
        }

        /*---------------------------------------------*/        
    }

    public function down() {
        $data['stt_key_name'] = 'tax_rate';
        $data['stt_key_value'] = '1.13';
        $data['stt_key_validate'] = 'numeric|floatval';
        $data['stt_section'] = 'Prices';
        $data['stt_label'] = 'Tax Rate (HST)';
        $this->db->insert('settings', $data);
        /*---------------------------------------------*/

        $this->db->where(['stt_key_name' => "tax"]);
        $this->db->delete('settings');
        
        /*---------------------------------------------*/
        $this->db->where(['stt_key_name' => "tax_name"]);
        $this->db->delete('settings');
    }

}
