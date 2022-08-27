<?php

class Migration_alter_from_name_and_to_name_columns_in_chat_table extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE chat MODIFY COLUMN from_name varchar(100) NULL ");
		$this->db->query("ALTER TABLE chat MODIFY COLUMN to_name varchar(100) NULL  ");
    }

    public function down() {
        
    }

}