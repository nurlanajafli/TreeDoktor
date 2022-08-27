<?php

class Migration_create_user_devices_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'device_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 50
            ),
            
            'device_user_id' => array(
                'type' => 'INT',
                'constraint' => 11
            ),

            'device_token' => array(
                'type' => 'TEXT'
            )
        ));

        $this->dbforge->add_field("`device_token_expiration` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");

        $this->dbforge->add_key('device_id', TRUE);
        $this->dbforge->add_key('device_user_id', TRUE);
        $this->dbforge->create_table('user_devices');
    }

    public function down() {
        $this->dbforge->drop_table('user_devices', true);
    }

}