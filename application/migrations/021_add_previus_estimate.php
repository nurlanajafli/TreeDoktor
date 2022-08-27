<?php

class Migration_add_previus_estimate extends CI_Migration {

    public function up() {
        
        $fields = array(
            'preliminary_estimate' => array(
                'type' => 'ENUM',
                'constraint' => ['small', 'medium', 'big'],
                'null' => TRUE
            )
        );

        $this->dbforge->add_column('leads', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('leads', 'preliminary_estimate');
    }

}
