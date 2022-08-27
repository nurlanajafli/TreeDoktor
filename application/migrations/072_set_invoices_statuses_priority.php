<?php

class Migration_set_invoices_statuses_priority extends CI_Migration {

    public function up() {
        $this->db->update('invoice_statuses', ['priority'=>1], ['invoice_status_id'=>1]); //Issued -> default
        $this->db->update('invoice_statuses', ['priority'=>4], ['invoice_status_id'=>2]); //Overdue -> is_overdue
        $this->db->update('invoice_statuses', ['priority'=>3], ['invoice_status_id'=>3]); //Sent -> is_sent
        $this->db->update('invoice_statuses', ['priority'=>5], ['invoice_status_id'=>4]); //Paid -> completed
        $this->db->update('invoice_statuses', ['priority'=>2], ['invoice_status_id'=>5]); //Hold Backs->is_hold_backs
    }

    public function down() {
        $this->db->update('invoice_statuses', ['priority'=>0], ['invoice_status_id'=>1]); //Issued -> default
        $this->db->update('invoice_statuses', ['priority'=>0], ['invoice_status_id'=>2]); //Overdue -> is_overdue
        $this->db->update('invoice_statuses', ['priority'=>0], ['invoice_status_id'=>3]); //Sent -> is_sent
        $this->db->update('invoice_statuses', ['priority'=>0], ['invoice_status_id'=>4]); //Paid -> completed
        $this->db->update('invoice_statuses', ['priority'=>0], ['invoice_status_id'=>5]); //Hold Backs->is_hold_backs
    }

}