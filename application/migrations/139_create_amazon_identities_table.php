<?php

class Migration_create_amazon_identities_table extends CI_Migration {

    public function up() {
        //$this->dbforge->drop_table('amazon_identities');
        $this->dbforge->add_field(array(
            'identity_id' => array(
                'type' => 'bigint',
                'constraint' => 20,
                'auto_increment' => TRUE
            ),

            'user_id' => array(
                'type' => 'bigint',
                'constraint' => 20
            ),

            'identity' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => TRUE
            ),

            // identity can be email OR domain
            'is_domain' => array(
                'type' => 'ENUM',
                'constraint' => ['1', '0'],
                'default' => '0'
            ),

            'dkimAttributes' => array(
                'type' => 'JSON',
                'null' => TRUE,
            ),

            'verificationAttributes' => array(
                'type' => 'JSON',
                'null' => TRUE,
            )
        ));

        $this->dbforge->add_key('identity_id', TRUE);
        $this->dbforge->create_table('amazon_identities', true);
        $this->db->query('ALTER TABLE `amazon_identities` ADD UNIQUE INDEX(`identity`) COMMENT \'Aws identity cannot be repeated\';');
    }

    public function down() {
        $this->dbforge->drop_table('amazon_identities');
    }
}
