<?php

class Migration_add_last_checked_column_to_amazon_identities_table extends CI_Migration {

    public function up() {
        $sql = 'ALTER TABLE `amazon_identities` ADD COLUMN `last_checked` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP';
        $this->db->query($sql);
    }

    public function down() {
        $sql = 'ALTER TABLE `amazon_identities` DROP COLUMN `last_checked`';
        $this->db->query($sql);
    }

}