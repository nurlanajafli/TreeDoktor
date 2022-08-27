<?php

class Migration_create_email_tracking_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field([
            'track_id' => [
                'type' => 'bigint',
                'constraint' => 20,
                'auto_increment' => TRUE
            ],
            'user_id' => [
                'type' => 'bigint',
                'constraint' => 20
            ],
            'domain' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'track_status' => [
                'type' => 'ENUM',
                'constraint' => ['accepted', 'rejected', 'delivered', 'failed', 'opened', 'clicked', 'unsubscribed', 'complained', 'stored'],
                'default' => 'accepted',
                'null' => false,
            ],
            'recipients' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'message' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ]
        ]);
        $this->dbforge->add_key('track_id', TRUE);
        $this->dbforge->create_table('email_tracking');
    }

    public function down() {
        $this->dbforge->drop_table('email_tracking');
    }

}
