<?php

class Migration_alter_client_email_column_in_clients_table extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE clients MODIFY COLUMN `client_email` varchar(100) NULL");
    }

    public function down() {}

}