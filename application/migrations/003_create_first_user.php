<?php

class Migration_create_first_user extends CI_Migration {

    public function __construct()
    {
        parent::__construct();
        $this->load->config('migration');
    }

    public function up() {
        if(config_item('install_migrations_need')==FALSE)
            return true;

        $user = [];
        for($i=0; $i<2; $i++){
            if(!$this->exist_user($i)){
                $this->insert_user($i);
            }
            if($i==0){
                $this->db->update('users', ['id'=>0], ['emailid'=>'system']);
            }
        }
        

        return true;
    }

    public function down() {
        if(config_item('install_migrations_need')==FALSE)
            return true;
        for($i=0; $i<2; $i++){
            $where = config_item('users')[$i];
            unset($where['added_on']);
            $this->db->where($where);
            $this->db->delete('users');
        }
    }

    private function exist_user($key)
    {
        $where = config_item('users')[$key];
        unset($where['added_on']);
        $this->db->where($where);
        $result = $this->db->get('users')->result();
        if(is_array($result) && !empty($result))
            return true;

        return false;
    }

    private function insert_user($key)
    {
        $data = config_item('users')[$key];
        $insert = $this->db->insert('users', $data);
        if ($this->db->affected_rows() > 0)
            return $this->db->insert_id();
        
        return FALSE;
    }

}