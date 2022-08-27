<?php

class Migration_add_event_index extends CI_Migration {

    public function up() {
        $sql = "ALTER TABLE `events` ADD INDEX(`ev_event_id`)";
        $this->db->query($sql);
    }

    public function down() {
        $sql = "ALTER TABLE `events` DROP INDEX `ev_event_id`";
        $this->db->query($sql);
    }

}