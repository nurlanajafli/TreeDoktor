<?php

class Migration_change_encoding_message_column_in_chat_table extends CI_Migration {

    public function up() {
        $this->db->query('ALTER TABLE chat MODIFY message TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    }

    public function down() {
        $this->db->query('ALTER TABLE chat MODIFY message TEXT CHARACTER SET utf8 COLLATE utf8_general_ci');
    }

}