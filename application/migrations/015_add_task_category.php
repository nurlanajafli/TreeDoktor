<?php

class Migration_add_task_category extends CI_Migration {

    public function up() {
        $query = $this->db->get_where('client_task_categories', ['category_name' => 'Busy']);
        
        if($query->num_rows()==0)
        {
            $data = ['category_id' => '0', 'category_name'=>'Busy', 'category_color'=>'#de3a27', 'category_active' => 1];
            $this->db->insert('client_task_categories', $data);
            $this->db->update('client_task_categories', ['category_id'=>0], ['category_name'=>'Busy']);
        }
    }

    public function down() {
        $where = ['category_id' => '0'];
        $this->db->where($where);
        $this->db->delete('client_task_categories');
        echo $this->db->last_query();
    }

}