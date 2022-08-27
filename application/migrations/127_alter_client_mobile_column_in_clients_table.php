<?php

class Migration_alter_client_mobile_column_in_clients_table extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE clients MODIFY COLUMN `client_mobile` varchar(30) NULL");
    }

    public function down() {}

}