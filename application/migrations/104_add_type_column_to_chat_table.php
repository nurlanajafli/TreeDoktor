<?php

class Migration_add_type_column_to_chat_table extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE chat ADD COLUMN type ENUM('text',  'doc', 'excel', 'image', 'pdf') DEFAULT 'text' ");
    }

    public function down() {
        $this->db->query('ALTER TABLE chat DROP COLUMN type');
    }

}
