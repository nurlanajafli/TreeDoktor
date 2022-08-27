<?php

class Migration_add_is_view_in_pdf_column_to_services_table extends CI_Migration {

    public function up() {
        $field = [
            'is_view_in_pdf' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default'=> 0
            ]
        ];
        $this->dbforge->add_column('services', $field);
    }

    public function down() {
        $this->dbforge->drop_column('services', 'is_view_in_pdf');
    }

}