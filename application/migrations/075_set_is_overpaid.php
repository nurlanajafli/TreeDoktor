<?php

class Migration_set_is_overpaid extends CI_Migration {

    public function up() {
        $this->db->update('invoice_statuses', ['is_overpaid'=>1], ['completed'=>1]);
    }

    public function down() {
        $this->db->update('invoice_statuses', ['is_overpaid'=>0], ['completed'=>1]);
    }

}