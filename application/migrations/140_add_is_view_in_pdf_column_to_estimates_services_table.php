<?php

class Migration_add_is_view_in_pdf_column_to_estimates_services_table extends CI_Migration {

    public function up() {
        $field = [
            'is_view_in_pdf' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default'=> 0
            ]
        ];
        $this->dbforge->add_column('estimates_services', $field);
    }

    public function down() {
        $this->dbforge->drop_column('estimates_services', 'is_view_in_pdf');
    }

}