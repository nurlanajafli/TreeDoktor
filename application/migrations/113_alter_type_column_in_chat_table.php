<?php

class Migration_alter_type_column_in_chat_table extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE chat MODIFY COLUMN type ENUM('text',  'doc', 'excel', 'image', 'pdf', 'file') DEFAULT 'text'");
    }

    public function down() {
        $this->db->query('ALTER TABLE chat DROP COLUMN type');
    }

}