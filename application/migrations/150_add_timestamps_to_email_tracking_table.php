<?php

class Migration_add_timestamps_to_email_tracking_table extends CI_Migration {

    public function up() {
        $sql = 'ALTER TABLE `email_tracking` 
            MODIFY COLUMN `message` text NOT NULL,
            ADD COLUMN `created_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
            ADD COLUMN `updated_at` timestamp(0) NULL ON UPDATE CURRENT_TIMESTAMP(0);';
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('email_tracking', 'created_at');
        $this->dbforge->drop_column('email_tracking', 'updated_at');
    }
}
