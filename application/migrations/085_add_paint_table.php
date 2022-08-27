<?php

class Migration_add_paint_table extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'paint_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'paint_path' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
            ),
            'paint_source_path' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
            ),
            'paint_source_data' => array(
                'type' => 'JSON',
                'null' => FALSE
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
        ));
        $this->dbforge->add_key('paint_id', TRUE);
        $this->dbforge->add_key('paint_path');
        $this->dbforge->add_key('paint_source_path');
        $this->dbforge->create_table('paint');
    }

    public function down() {
        $this->dbforge->drop_table('paint', true);
    }

}
