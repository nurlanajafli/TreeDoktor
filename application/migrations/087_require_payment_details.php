<?php

class Migration_require_payment_details extends CI_Migration {

    public function up() {
        $fields = [
            'is_require_payment_details' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ];

        $this->dbforge->add_column('users', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('users', 'is_require_payment_details');
    }

}