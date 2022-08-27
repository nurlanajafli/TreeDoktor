<?php

class Migration_estimate_services_to_lead extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'lead_id' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'services_id' => array(
                'type' => 'INT',
                'constraint' => 11
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('lead_services');
    }

    public function down() {
        $this->dbforge->drop_table('lead_services', true);
    }

}
