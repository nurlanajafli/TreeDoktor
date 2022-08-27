<?php

class Migration_add_estimate_qb_id_column_to_estimates_table extends CI_Migration {

    public function up() {
        $fields = array(
            'estimate_qb_id' => array(
                'type' => 'INT',
                'null' => true
            ),
        );
        $this->dbforge->add_column('estimates', $fields);
        $sql = "CREATE INDEX estimate_qb_id ON estimates(estimate_qb_id)";
        $this->db->query($sql);
    }

    public function down() {
        $this->dbforge->drop_column('estimates', 'estimate_qb_id');
    }

}