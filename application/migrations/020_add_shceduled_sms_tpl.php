<?php

class Migration_add_shceduled_sms_tpl extends CI_Migration {

    public function up() {
        $query = $this->db->get_where('sms_tpl', ['system_label' => 'estimator_schedule_appointment']);
        if($query->num_rows()==0)
        {
            $data_estimator = [
                'sms_name' => 'Estimator - Schedule appointment',
                'sms_text'=> 'Hello, [NAME]. You have new schedule appointment.',
                'user' => NULL,
                'system_label' => 'estimator_schedule_appointment'
            ];
            $this->db->insert('sms_tpl', $data_estimator);
        }

        $query = $this->db->get_where('sms_tpl', ['system_label' => 'client_schedule_appointment']);
        if($query->num_rows()==0)
        {
            $data_client = [
                'sms_name' => 'Client - Schedule appointment',
                'sms_text'=> 'Hello, [NAME]. This is an automatic message. Your have new appointment at {address}. Thank you.',
                'user' => NULL,
                'system_label' => 'client_schedule_appointment'
            ];
        }
        
        $this->db->insert('sms_tpl', $data_client);
    }

    public function down() {
        
        $where = ['system_label' => 'estimator_schedule_appointment'];
        $this->db->where($where);
        $this->db->delete('sms_tpl');
        

        $where = ['system_label' => 'client_schedule_appointment'];
        $this->db->where($where);
        $this->db->delete('sms_tpl');
        
        echo $this->db->last_query();
        
    }

}