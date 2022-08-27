<?php

class Migration_patch_lead_serv_est_serv extends CI_Migration {

    public function up() {
        $leads = $this->db->query('SELECT * FROM leads WHERE tree_removal = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 4]);
        $leads = $this->db->query('SELECT * FROM leads WHERE tree_pruning = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 19]);
        $leads = $this->db->query('SELECT * FROM leads WHERE hedge_maintenance = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 21]);
        $leads = $this->db->query('SELECT * FROM leads WHERE wood_disposal = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 8]);
        $leads = $this->db->query('SELECT * FROM leads WHERE stump_removal = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 11]);
        $leads = $this->db->query('SELECT * FROM leads WHERE root_fertilizing = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 24]);
        $leads = $this->db->query('SELECT * FROM leads WHERE spraying = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 12]);
        $leads = $this->db->query('SELECT * FROM leads WHERE trunk_injection = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 22]);
        $leads = $this->db->query('SELECT * FROM leads WHERE air_spading = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 37]);
        $leads = $this->db->query('SELECT * FROM leads WHERE air_spading = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 17]);
        $leads = $this->db->query('SELECT * FROM leads WHERE arborist_consultation = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 1]);
        $leads = $this->db->query('SELECT * FROM leads WHERE arborist_report = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 2]);
        $leads = $this->db->query('SELECT * FROM leads WHERE construction_arborist_report = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 3]);
        $leads = $this->db->query('SELECT * FROM leads WHERE tree_cabling = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 13]);
        $leads = $this->db->query('SELECT * FROM leads WHERE tpz_installation = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 23]);
        $leads = $this->db->query('SELECT * FROM leads WHERE lights_installation = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 18]);
        $leads = $this->db->query('SELECT * FROM leads WHERE snow_removal = "yes"')->result();
		foreach($leads as $k=>$v)
			$insert = $this->db->insert('lead_services', ['lead_id' => $v->lead_id, 'services_id' => 40]);
		
    }

    public function down() {
        $this->db->query('TRUNCATE  lead_services');
    }

}
