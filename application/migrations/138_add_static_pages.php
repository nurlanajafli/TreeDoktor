<?php

class Migration_add_static_pages extends CI_Migration {

    public function up() {


        $this->dbforge->add_field([
            'sp_id'         => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE],
            'sp_slug'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => FALSE],
            'sp_content'=> ['type' => 'TEXT', 'null' => FALSE],
            'sp_active'    => ['type' => 'TINYINT', 'constraint' => 1, 'null' => FALSE, 'default' => 1],
            'sp_created_date'         => ['type' => 'DATETIME', 'null' => FALSE],
        ]);

        $this->dbforge->add_key('sp_id', TRUE);
        $this->dbforge->create_table('static_pages', TRUE);
    }

    public function down() {
        $this->dbforge->drop_table('static_pages', TRUE);
    }

}