<?php

class Migration_rename_prune_types extends CI_Migration {

    public function up() {
        $this->dbforge->rename_table('inventory_prune_types', 'work_types');
    }

    public function down() {
        $this->dbforge->rename_table('work_types', 'inventory_prune_types');
    }

}