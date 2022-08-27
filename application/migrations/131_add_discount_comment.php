<?php

class Migration_add_discount_comment extends CI_Migration {

    public function up() {
        $fields = array(
            'discount_comment' => array(
                'type' => 'TEXT',
                'null' => true                
            ),
        );
        $this->dbforge->add_column('discounts', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('discounts', 'discount_comment');
    }

}