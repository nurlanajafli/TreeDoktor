<?php

class Migration_install_structure extends CI_Migration {
    public function __construct()
    {
        parent::__construct();
        $this->load->config('migration');
        $this->load->helper('file');
    }

    public function up() {

        if(config_item('install_migrations_need')==FALSE)
            return true;

        $sql = read_file(config_item('migration_install_path'));
        
        $query = explode(';', $sql);
        array_pop($query);

        foreach($query as $statement){
            $statment = $statement . ";";
            $this->db->query($statement);   
        }

    }

    public function down() {
        
        if(config_item('install_migrations_need')==FALSE)
            return true;
        
        $sql = read_file(config_item('migration_uninstall_path'));
        $query = explode(';', $sql);
        array_pop($query);

        foreach($query as $statement){
            $statment = $statement . ";";
            $this->db->query($statement);   
        }
        return true;
    }

}