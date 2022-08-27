<?php

class Migration_alter_client_web_column_in_clients_table extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE clients MODIFY COLUMN `client_web` varchar(100) NULL");
    }

    public function down() {}

}