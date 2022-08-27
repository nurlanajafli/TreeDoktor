<?php

class Migration_update_leads_reason_status extends CI_Migration {

    public function up() {
       $fields = array(
            'lead_reason_status' => array(
                'type' => 'ENUM',
                'constraint' => ['Don\'t provide this service', 'Out of service area', 'Don\'t want work done anymore', 'Already Done', 'Dublicate lead', 'Hydro', 'Dangerous tree no access', 'Spam', 'Already hired someone else', 'The lead is not responding'],
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('leads', $fields);
    }

    public function down() {
        $fields = array(
            'lead_reason_status' => array(
                'type' => 'ENUM',
                'constraint' => ['Don\'t provide this service', 'Out of service area', 'Don\'t want work done anymore', 'Already Done', 'Dublicate lead', 'Hydro', 'Dangerous tree no access', 'Spam'],
                'null' => TRUE
            )
        );

        
        $this->dbforge->modify_column('leads', $fields);
    }

}
