<?php

class Migration_add_prune_types extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'ip_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'ip_name_short' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ),
            'ip_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            )
        ));

        $this->dbforge->add_key('ip_id', TRUE);
        $this->dbforge->create_table('inventory_prune_types');

        $data = [
            ['ip_id' => null, 'ip_name_short'=>'CC', 'ip_name'=>'Clean canopy'],
            ['ip_id' => null, 'ip_name_short'=>'RB', 'ip_name'=>'Release building'],
            ['ip_id' => null, 'ip_name_short'=>'CR', 'ip_name'=>'Crown reduction'],
            ['ip_id' => null, 'ip_name_short'=>'RL', 'ip_name'=>'Release light'],
            ['ip_id' => null, 'ip_name_short'=>'CRL', 'ip_name'=>'Crown reduction lateral'],
            ['ip_id' => null, 'ip_name_short'=>'RR', 'ip_name'=>'Release road'],
            ['ip_id' => null, 'ip_name_short'=>'DW', 'ip_name'=>'Deadwood'],
            ['ip_id' => null, 'ip_name_short'=>'RS', 'ip_name'=>'Remove stake'],
            ['ip_id' => null, 'ip_name_short'=>'EW', 'ip_name'=>'End weight reduction'],
            ['ip_id' => null, 'ip_name_short'=>'S', 'ip_name'=>'Shape'],
            ['ip_id' => null, 'ip_name_short'=>'L', 'ip_name'=>'Lift'],
            ['ip_id' => null, 'ip_name_short'=>'SG', 'ip_name'=>'Stump grinding'],
            ['ip_id' => null, 'ip_name_short'=>'PB', 'ip_name'=>'Prune for balance'], 
            ['ip_id' => null, 'ip_name_short'=>'SM', 'ip_name'=>'Size maintenance'],
            ['ip_id' => null, 'ip_name_short'=>'PS', 'ip_name'=>'Prune for structure'], 
            ['ip_id' => null, 'ip_name_short'=>'TC', 'ip_name'=>'Thin canopy'],
            ['ip_id' => null, 'ip_name_short'=>'XX', 'ip_name'=>'Remove']
        ];
        foreach ($data as $key => $row) {
            $this->db->insert('inventory_prune_types', $row);    
        }
        
    }

    public function down() {
        $this->dbforge->drop_table('inventory_prune_types', true);
    }

}