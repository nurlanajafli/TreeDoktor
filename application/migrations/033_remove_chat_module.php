<?php

class Migration_remove_chat_module extends CI_Migration {

    public function up() {
        //'DELETE FROM `modules_master` WHERE `module_id` = "CHAT"';
        $this->db->where(['module_id' => "CHAT"]);
		$this->db->delete('modules_master');
    }

    public function down() {
		//'INSERT INTO `modules_master` (`id`, `module_id`, `module_desc`) VALUES ("", "CHAT", "Live Chat")';
		$data['module_id'] = 'CHAT';
		$data['module_desc'] = 'Live Chat';
		$this->db->insert('modules_master', $data);
    }

}
