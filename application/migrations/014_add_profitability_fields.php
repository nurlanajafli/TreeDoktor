<?php

class Migration_add_profitability_fields extends CI_Migration {

    public function up() {
        $fields = [
			'vehicle_per_hour_price' => [
			    'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ]
		];
		$this->dbforge->add_column('vehicles', $fields);


        /*

        + INDEX
        */

        $fields = [
            'estimate_planned_company_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estimate_planned_crews_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estimate_planned_equipments_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estimate_planned_extra_expenses' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estimate_planned_overheads_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estimate_planned_profit' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estimate_planned_profit_percents' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estimate_planned_tax' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estimate_planned_total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estimate_planned_total_for_services' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
        ];
        $this->dbforge->add_column('estimates', $fields);

        $fields = [
            'service_overhead_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'service_markup_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
        ];
        $this->dbforge->add_column('estimates_services', $fields);


        $fields = [
            'event_expenses' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ]
        ];
        $this->dbforge->add_column('schedule', $fields);


        $fields = [
            'crew_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ]
        ];
        $this->dbforge->modify_column('crews', $fields);


        $fields = [
            'service_cost' => [
                'name' => 'service_markup',
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ]
        ];
        $this->dbforge->modify_column('services', $fields);


        $this->dbforge->add_field(array(
            'ese_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),

            'ese_estimate_service_id' => array(
                'type' => 'INT',
                'constraint' => 11
            ),

            'ese_estimate_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),

            'ese_title' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ),

            'ese_price' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            )
        ));
        $this->dbforge->add_key('ese_id', TRUE);
        $this->dbforge->add_key('ese_estimate_service_id');
        $this->dbforge->add_key('ese_estimate_id');
        $this->dbforge->create_table('estimates_services_expenses');
    }

    public function down() {
        $this->dbforge->drop_column('vehicles', 'vehicle_per_hour_price');

        $this->dbforge->drop_column('estimates', 'estimate_planned_company_cost');
        $this->dbforge->drop_column('estimates', 'estimate_planned_crews_cost');
        $this->dbforge->drop_column('estimates', 'estimate_planned_equipments_cost');
        $this->dbforge->drop_column('estimates', 'estimate_planned_extra_expenses');
        $this->dbforge->drop_column('estimates', 'estimate_planned_overheads_cost');
        $this->dbforge->drop_column('estimates', 'estimate_planned_profit');
        $this->dbforge->drop_column('estimates', 'estimate_planned_profit_percents');
        $this->dbforge->drop_column('estimates', 'estimate_planned_tax');
        $this->dbforge->drop_column('estimates', 'estimate_planned_total');
        $this->dbforge->drop_column('estimates', 'estimate_planned_total_for_services');

        $this->dbforge->drop_column('estimates_services', 'service_overhead_rate');
        $this->dbforge->drop_column('estimates_services', 'service_markup_rate');
        $this->dbforge->drop_column('schedule', 'event_expenses');

        $fields = [
            'crew_rate' => [
                'type' => 'INT',
                'constraint' => 11
            ]
        ];
        $this->dbforge->modify_column('crews', $fields);

        $fields = [
            'service_markup' => [
                'name' => 'service_cost',
                'type' => 'float',
                'null' => TRUE
            ]
        ];
        $this->dbforge->modify_column('services', $fields);

        $this->dbforge->drop_table('estimates_services_expenses', true);
    }

}
