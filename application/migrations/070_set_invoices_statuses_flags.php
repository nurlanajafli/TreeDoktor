<?php

class Migration_set_invoices_statuses_flags extends CI_Migration {

    public function up() {
        $this->db->update('invoice_statuses', ['default'=>1, 'protected'=>1], ['invoice_status_id'=>1]); //Issued -> default
        $this->db->update('invoice_statuses', ['protected'=>1, 'is_overdue'=>1], ['invoice_status_id'=>2]); //Overdue -> is_overdue
        $this->db->update('invoice_statuses', ['protected'=>1, 'is_sent'=>1], ['invoice_status_id'=>3]); //Sent -> is_sent
        $this->db->update('invoice_statuses', ['protected'=>1, 'completed'=>1], ['invoice_status_id'=>4]); //Paid -> completed
        $this->db->update('invoice_statuses', ['protected'=>1, 'is_hold_backs'=>1], ['invoice_status_id'=>5]); //Hold Backs->is_hold_backs
    }

    public function down() {
        $this->db->update('invoice_statuses', ['default'=>0, 'protected'=>0], ['invoice_status_id'=>1]); //Issued -> default
        $this->db->update('invoice_statuses', ['protected'=>0, 'is_overdue'=>0], ['invoice_status_id'=>2]); //Overdue -> is_overdue
        $this->db->update('invoice_statuses', ['protected'=>0, 'is_sent'=>0], ['invoice_status_id'=>3]); //Sent -> is_sent
        $this->db->update('invoice_statuses', ['protected'=>0, 'completed'=>0], ['invoice_status_id'=>4]); //Paid -> completed
        $this->db->update('invoice_statuses', ['protected'=>0, 'is_hold_backs'=>0], ['invoice_status_id'=>5]); //Hold Backs->is_hold_backs
    }

}