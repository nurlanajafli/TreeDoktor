<?php

class Migration_add_driver_to_email_tracking_table extends CI_Migration {

    public function up() {
        $fields = array(
            'driver' => [
                'type' => 'ENUM',
                'constraint' => "'mailgun', 'amazon', 'smtp', 'sendmail'",
                'null' => false,
            ],
        );
        $this->dbforge->add_column('email_tracking', $fields);

        $sql = "ALTER TABLE `email_tracking` 
            MODIFY COLUMN `track_status` enum('accepted','rejected','delivered','failed','opened','clicked','unsubscribed','complained','stored','bounce') 
            NOT NULL DEFAULT 'accepted';";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('email_tracking', 'driver');
    }
}
