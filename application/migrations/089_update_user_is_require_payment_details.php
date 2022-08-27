<?php

class Migration_update_user_is_require_payment_details extends CI_Migration {

    public function up() {
        $fields = [
            'is_require_payment_details' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
        ];

        $this->dbforge->modify_column('users', $fields);
        $this->db->update('users', ['is_require_payment_details'=>1], ['is_require_payment_details'=>0, 'user_type'=>'user']);
    }

    public function down() {
        $fields = [
            'is_require_payment_details' => [
                'type' =>'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ];

        $this->dbforge->modify_column('users', $fields);
        $this->db->update('users', ['is_require_payment_details'=>0], ['is_require_payment_details'=>1]);
    }

}