<?php

class Migration_add_invoice_statuses_data extends CI_Migration {

    public function up() {
        $data = array( 
						array('invoice_status_id' => 1, 'invoice_status_name' => 'Issued', 'invoice_status_active' => 1),
						array('invoice_status_id' => 2, 'invoice_status_name' => 'Overdue', 'invoice_status_active' => 1),
						array('invoice_status_id' => 3, 'invoice_status_name' => 'Sent', 'invoice_status_active' => 1),
						array('invoice_status_id' => 4, 'invoice_status_name' => 'Paid', 'invoice_status_active' => 1),
						array('invoice_status_id' => 5, 'invoice_status_name' => 'Hold Backs', 'invoice_status_active' => 1)
						);
		foreach($data as $k=>$v)
			$insert = $this->db->insert('invoice_statuses', $v);
		
		$this->db->query('update invoices i join invoice_statuses s on i.in_status = s.invoice_status_name set i.in_status = s.invoice_status_id');
		$this->db->query("update status_log sl join invoice_statuses s on sl.status_value = s.invoice_status_name set sl.status_value = s.invoice_status_id WHERE status_type = 'invoice'");
    }

    public function down() {
		 $data = array( 
						array('invoice_status_id' => 1, 'invoice_status_name' => 'Issued', 'invoice_status_active' => 1),
						array('invoice_status_id' => 2, 'invoice_status_name' => 'Overdue', 'invoice_status_active' => 1),
						array('invoice_status_id' => 3, 'invoice_status_name' => 'Sent', 'invoice_status_active' => 1),
						array('invoice_status_id' => 4, 'invoice_status_name' => 'Paid', 'invoice_status_active' => 1),
						array('invoice_status_id' => 5, 'invoice_status_name' => 'Hold Backs', 'invoice_status_active' => 1)
						);
		foreach($data as $k=>$v)
		{
			$this->db->query('update invoices i join invoice_statuses s on i.in_status = s.invoice_status_id set i.in_status = s.invoice_status_name');
			$this->db->query("update status_log sl join invoice_statuses s on sl.status_value = s.invoice_status_id set sl.status_value = s.invoice_status_name WHERE status_type = 'invoice'");
			$this->db->where($v);
			$this->db->delete('invoice_statuses');
		}
        echo $this->db->last_query();
    }

}
