<?php

class Migration_change_clients_fields_types extends CI_Migration {

    public function up() {
        $fields = array(
            'call_workspace_sid' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
            ),
        );
        $this->dbforge->modify_column('clients_calls', $fields);
        


        $fields = array(
            'absence_employee_id' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => TRUE,
            ),
        );
        $this->dbforge->modify_column('schedule_absence', $fields);

    }

    public function down() {
        $fields = array(
            'call_workspace_sid' => array(
                'type' => 'ENUM',
                'constraint' => ['WS5107ffee9f1d1715ed06b8da32361790','WSd5ddf64bb22aa165abac6c6434764dec'],
                'null' => TRUE,
            )
        );

        
        $this->dbforge->modify_column('clients_calls', $fields);


        $fields = array(

            'absence_employee_id' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => FALSE,
            ),
        );
        $this->dbforge->modify_column('schedule_absence', $fields);
    }

}
