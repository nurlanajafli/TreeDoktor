<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Mdl_invoices extends MY_Model
{

    function __construct()
    {
        parent::__construct();

        $this->table = 'invoices';
        $this->primary_key = "invoices.id";
    }

//*******************************************************************************************************************
//*************
//*************			Get Invoices Function; Returns row() or false; 
//*************
//*******************************************************************************************************************	
    /* delete if all ok 09.05.2012
    function get_invoices($search_keyword, $status, $limit, $start, $wdata = array(), $filter = FALSE)
    {
        $services = "";
        $contactsJoin = " AND clients_contacts.cc_print = 1";
        if($status == 4)
            $services = "  AND service_status = 2";


        $sql_query = "SELECT 					clients.client_id,
                                                clients.client_name,
                                                clients.client_contact,
                                                clients.client_address,
                                                clients.client_city,
                                                clients.client_zip,
                                                clients_contacts.cc_phone,

                                                leads.lead_address as client_address,
                                                leads.lead_city as client_city,
                                                leads.lead_state as client_state,

                                                leads.latitude,
                                                leads.longitude,

                                                estimates.estimate_id,
                                                estimates.estimate_hst_disabled,


                                                ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) / IF(estimate_hst_disabled = 2, " . config_item('tax_rate') . ", 1) , 2) as total,
                                                ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) * IF(estimate_hst_disabled = 0, " . config_item('tax_rate') . ", 1) , 2) as total_with_hst,
                                                (ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
                                                - IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / " . config_item('tax_rate') . ",	ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	* discounts.discount_amount / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
                                                 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/ IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2))
                                                * IF(estimate_hst_disabled = 0, " . config_item('tax_rate') . ", 1) - (SUM(IF(client_payments.payment_amount, client_payments.payment_amount, 0)) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1))  as due,


                                                estimates_qa.qa_id,

                                                users.firstname,
                                                users.lastname,
                                                users.emailid,

                                                invoices.id,
                                                invoices.invoice_no,
                                                invoices.invoice_like,
                                                invoices.invoice_notes,
                                                invoices.workorder_id,
                                                invoices.interest_status,
                                                invoices.interest_rate,
                                                invoices.date_created,
                                                invoices.in_status,DATEDIFF(NOW(),invoices.date_created) as days,
                                                invoices.overdue_date,
                                                invoice_statuses.invoice_status_name";


        $sql_query .= "				FROM 		invoices

                                    INNER JOIN 	estimates

                                    ON 			invoices.estimate_id= estimates.estimate_id

                                    LEFT JOIN invoice_interest

                                    ON 			invoices.id= invoice_interest.invoice_id

                                    LEFT JOIN invoice_statuses

                                    ON 			invoices.in_status = invoice_statuses.invoice_status_id

                                    INNER JOIN 	leads

                                    ON 			estimates.lead_id= leads.lead_id

                                    INNER JOIN 	clients

                                    ON 			estimates.client_id = clients.client_id

                                    LEFT JOIN 	clients_contacts

                                    ON 			clients.client_id = clients_contacts.cc_client_id $contactsJoin

                                    LEFT JOIN 	estimates_qa

                                    ON			estimates_qa.estimate_id=invoices.estimate_id

                                    LEFT JOIN 	estimates_services

                                    ON			estimates_services.estimate_id=invoices.estimate_id $services

                                    LEFT JOIN 	discounts

                                    ON			discounts.estimate_id=invoices.estimate_id

                                    LEFT JOIN client_payments

                                    ON			client_payments.estimate_id=invoices.estimate_id

                                    INNER JOIN users

                                    ON			estimates.user_id=users.id

                                    WHERE 1=1 ";

        if (isset($search_keyword) && $search_keyword != "") {
            $sql_query .= " AND (clients.client_name LIKE '%" . $search_keyword . "%'
                                OR clients_contacts.cc_name LIKE '%" . $search_keyword . "%'
                                OR clients_contacts.cc_phone LIKE '%" . $search_keyword . "%'
                                OR clients.client_address LIKE '%" . $search_keyword . "%'
                                OR clients_contacts.cc_email LIKE '%" . $search_keyword . "%'
                                OR leads.lead_address LIKE '%" . $search_keyword . "%'
                                OR invoices.invoice_no LIKE '%" . $search_keyword . "%')";
        }

        if (isset($status) && $status != "") {
            $sql_query .= " AND invoices.in_status = '" . $status . "'";
        }

        if($wdata || count($wdata))
        {
            $sql_query .= " AND ";
            if(is_array($wdata))
            {
                $i = 0;
                foreach($wdata as $key => $val)
                {

                    if($val == NULL)
                        $sql_query .= $key . " IS NULL ";
                    else
                        $sql_query .= $key . " ='".$val."' ";
                    $i++;
                    if(count($wdata) > $i)
                        $sql_query .= " AND ";

                }
            }
            else
                $sql_query .= $wdata;
        }

        $sql_query .= ' GROUP BY invoices.estimate_id';
        if($filter && $filter !== 'false')
            $sql_query .= ' HAVING due > 0.1';
        $sql_query .= " ORDER BY id DESC";


        if ($start != '') {
            $sql_query .= " LIMIT " . $limit . ", " . $start;
        }

        $query = $this->db->query($sql_query);

        return $query;

    } // End get_leads();
    */
    function get_invoice($wdata){
        $result = $this->invoices($wdata);
        if(!$result || empty($result))
            return false;
        return array_shift($result);
    }
//*******************************************************************************************************************
//*************
//*************								Count invoice function AND NEW get_invoices
//*************
//*******************************************************************************************************************	
    function invoices($where = [], $search = false, $offset = 0, $limit = FALSE, $filter = false, $orderBy = 'date_created', $orderRule = 'desc')
    {
        $this->load->model('mdl_estimates_orm');
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery($where);
        $this->db->select("
			clients.client_id, clients.client_name, clients.client_contact, clients.client_address, clients.client_city,
			clients.client_zip, clients_contacts.cc_phone, clients_contacts.cc_name, clients_contacts.cc_email,
			
			leads.lead_address as client_address, leads.lead_city as client_city, leads.lead_state as client_state,
			leads.latitude, leads.longitude,
			
			estimates.estimate_hst_disabled, estimates_qa.qa_id,
			
			users.firstname, users.lastname, users.emailid, CONCAT(users.firstname, ' ', users.lastname) as estimator_name, 
			
			invoices.id, invoices.estimate_id, invoices.invoice_no, invoices.invoice_like, invoices.invoice_notes,
			invoices.workorder_id, invoices.interest_status, invoices.interest_rate, invoices.date_created, invoices.in_status, invoices.invoice_qb_id,
			invoices.invoice_last_qb_time_log, invoices.invoice_last_qb_sync_result, DATEDIFF(NOW(),invoices.date_created) as days, invoices.overdue_date,

			invoice_statuses.invoice_status_id, invoice_statuses.invoice_status_name,
			totals.*,
			totals.sum_without_tax as total,
			totals.total_with_tax as total_with_hst,
			totals.total_due as due", FALSE);


        $this->db->join('estimates', 'invoices.estimate_id= estimates.estimate_id');
        $this->db->join('invoice_interest', 'invoices.id= invoice_interest.invoice_id', 'left');
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
        $this->db->join('leads', 'estimates.lead_id= leads.lead_id');
        $this->db->join('clients', 'estimates.client_id = clients.client_id');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print=1', 'left');
        $this->db->join('estimates_qa', 'estimates_qa.estimate_id=invoices.estimate_id', 'left');
        $this->db->join('estimates_services', 'estimates_services.estimate_id=invoices.estimate_id AND (CASE WHEN invoice_statuses.completed=1 THEN estimates_services.service_status = 2 WHEN invoice_statuses.completed=0 THEN 1=1 END)', 'left', FALSE);
        $this->db->join('discounts', 'discounts.estimate_id=invoices.estimate_id', 'left');
        $this->db->join('client_payments', 'client_payments.estimate_id=invoices.estimate_id AND client_payments.payment_amount > 0', 'left');
        $this->db->join('users', 'estimates.user_id=users.id', 'left');
        $this->db->join("($totalsSubQuery) totals", 'invoices.estimate_id=totals.estimate_id');

        if (!empty($where))
            $this->db->where($where);

        if ($search) {
            $like = [
                "clients.client_name LIKE '%$search%'",
                "clients_contacts.cc_name LIKE '%$search%'",
                "clients.client_address LIKE '%$search%'",
                "clients_contacts.cc_email LIKE '%$search%'",
                "clients_contacts.cc_phone LIKE '%$search%'",
                "leads.lead_address LIKE  '%$search%'",
                "invoices.invoice_no LIKE '%$search%'"
            ];
            $this->db->where("(" . implode(" OR ", $like) . ")");
        }

        if ($filter)
            $this->db->having('due > ', 0.1, FALSE);

        $this->db->group_by('invoices.estimate_id');
        $this->db->order_by($orderBy, $orderRule);

        if ($limit !== FALSE)
            $this->db->limit($limit, $offset);

        $query = $this->db->get('invoices');

        if (!$query)
            return [];

        return $query->result();
    }

    function invoices_record_count($where = [], $search = false, $filter = false)
    {

        $this->db->select("invoices.estimate_id, invoice_statuses.invoice_status_id, COUNT(DISTINCT invoices.estimate_id) as total, (ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) - IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / estimate_tax_rate,	ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	* discounts.discount_amount / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0)) + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2)) * IF(estimate_hst_disabled = 0, estimate_tax_rate, 1) - (SUM(IF(client_payments.payment_amount, client_payments.payment_amount, 0)) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1))  as due", FALSE);

        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id');
        $this->db->join('estimates', 'invoices.estimate_id= estimates.estimate_id');
        $this->db->join('leads', 'estimates.lead_id= leads.lead_id');
        $this->db->join('invoice_interest', 'invoices.id= invoice_interest.invoice_id', 'left');
        $this->db->join('discounts', 'discounts.estimate_id=invoices.estimate_id', 'left');
        $this->db->join('client_payments', 'client_payments.estimate_id=invoices.estimate_id', 'left');
        $this->db->join('estimates_services', 'estimates_services.estimate_id=invoices.estimate_id AND (CASE WHEN invoice_statuses.completed=1 THEN estimates_services.service_status = 2 WHEN invoice_statuses.completed=0 THEN 1=1 END)', 'left', FALSE);
        $this->db->join('users', 'estimates.user_id=users.id', 'left');

        if (!empty($where))
            $this->db->where($where);

        if ($search) {
            $this->db->join('clients', 'estimates.client_id = clients.client_id');
            $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id', 'left');

            $like = [
                "clients.client_name LIKE '%$search%'",
                "clients_contacts.cc_name LIKE '%$search%'",
                "clients.client_address LIKE '%$search%'",
                "clients_contacts.cc_email LIKE '%$search%'",
                "clients_contacts.cc_phone LIKE '%$search%'",
                "leads.lead_address LIKE  '%$search%'",
                "invoices.invoice_no LIKE '%$search%'"
            ];
            $this->db->where("(" . implode(" OR ", $like) . ")");
        }

        if ($filter) {
            $this->db->from('invoices');
            $this->db->group_by('invoices.estimate_id');
            $this->db->having('due > ', 0.1, FALSE);

            $subquery = $this->db->_compile_select();
            $this->db->_reset_select();

            $this->db->select('invoices.estimate_id, subcount.invoice_status_id, COUNT(DISTINCT invoices.estimate_id) as total', false);
            $this->db->join("($subquery) subcount", "subcount.estimate_id=invoices.estimate_id");
        }

        $this->db->group_by('invoices.in_status');
        $query = $this->db->get('invoices');

        if (!$query)
            return [];

        return $query->result_array();
    }



//*******************************************************************************************************************
//*************
//*************			Insert Invoice Function; Returns insert id or false; 
//*************
//*******************************************************************************************************************	

    function insert_invoice($data)
    {

        if ($data) {

            $insert = $this->db->insert($this->table, $data);

            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                return FALSE;
            }
        } else {
            echo "data not received";
        }
    }

//*******************************************************************************************************************
//*************
//*************								Update invoice function
//*************
//*******************************************************************************************************************	

    function update_invoice($data, $wdata)
    {
        if ($data != '' && $wdata != '') {

            $this->db->where($wdata);
            $update = $this->db->update($this->table, $data);
            //echo $this->db->last_query();
            if ($this->db->affected_rows() > 0) {
                return $this->db->affected_rows();
            } else {
                return FALSE;
            }
        } else {
            echo "data not received";
        }
    }// End. update_estimates ();

//*******************************************************************************************************************
//*************
//*************								Get Client Invoice function
//*************
//*******************************************************************************************************************	


    function get_client_invoices($id)
    {

        $query = $this->db->query("	SELECT 		invoices.invoice_no,
												invoices.date_created,
												invoices.in_status,
												invoices.invoice_like,
												invoices.invoice_feedback,
												invoices.id,
												invoices.invoice_qb_id,
												invoices.estimate_id,
												invoices.invoice_last_qb_time_log,
												invoices.invoice_last_qb_sync_result,
												invoice_statuses.invoice_status_name,
												CONCAT(users.firstname, ' ', users.lastname) as emp_name

									FROM 		invoices
									
									INNER JOIN 	estimates
									
									ON 			invoices.estimate_id=estimates.estimate_id
									
									LEFT JOIN invoice_statuses
									
									ON 			invoices.in_status = invoice_statuses.invoice_status_id
									
									LEFT JOIN 	users
									
									ON 			estimates.user_id=users.id
									 
									INNER JOIN 	clients
									
									ON 			invoices.client_id=clients.client_id
									
									WHERE 		invoices.client_id = $id ");

        //if ($query->num_rows() > 0) {
        return $query;
        /*} else {
            return FALSE;
        }*/

    } //* get_client_invoices($id)

    function get_client_invoices_app($id)
    {
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['estimates.client_id' => $id]);
        $this->db->select('invoices.invoice_no, invoices.id, CONCAT(users.firstname, " ", users.lastname) as emp_name, invoices.date_created, invoice_statuses.invoice_status_name, invoice_statuses.invoice_status_id, estimates.estimate_id, totals.sum_without_tax as total, totals.total_due,', FALSE);
        $this->db->from('invoices');
        $this->db->join('estimates', 'invoices.estimate_id=estimates.estimate_id');
        $this->db->join('leads', 'leads.lead_id=estimates.lead_id');
        $this->db->join('users', 'estimates.user_id=users.id');
        $this->db->join('invoice_statuses', 'invoices.in_status=invoice_statuses.invoice_status_id');
        $this->db->join("($totalsSubQuery) totals", 'estimates.estimate_id = totals.estimate_id', 'left');
        $this->db->where('invoices.client_id', $id);
        $query = $this->db->get();
        return $query;
    }

    //*******************************************************************************************************************
//*************
//*************								Get Client Invoice data by attribute function
//*************
//*******************************************************************************************************************	


    function get_client_invoices_by_attribute($attr)
    {

        $query = $this->db->query("	SELECT 		invoices.invoice_no,
												invoices.workorder_id,
												invoices.estimate_id,
												invoices.date_created,
												invoices.in_status,
												invoices.id,
												invoice_statuses.invoice_status_name,
												clients.*,
												clients_contacts.*
												
									FROM 		invoices
									 
									INNER JOIN 	clients
									
									ON 			invoices.client_id=clients.client_id
									
									LEFT JOIN invoice_statuses
									
									ON 			invoices.in_status = invoice_statuses.invoice_status_id

									LEFT JOIN 	clients_contacts

									ON 			clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1
									
									WHERE 		" . $attr);

        //if ($query->num_rows() > 0) {
        return $query;
        /*} else {
            return FALSE;
        }*/

    } //* get_client_invoices($id)

//*******************************************************************************************************************
//*************
//*************													Delete Invoice function
//*************
//*******************************************************************************************************************	

    function delete_invoice($id)
    {
        if ($id) {
            $this->db->where('estimate_id', $id);
            $this->db->delete($this->table);

            if ($this->db->affected_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    //*******************************************************************************************************************
//*************
//*************													Delete Invoice function
//*************
//*******************************************************************************************************************	

        function getEstimatedData($id)
    {
        if ($id) {
            $sql_query = "SELECT
												
												estimates.*,
												leads.lead_address,
												leads.lead_city,
												leads.lead_state,
												leads.lead_zip,
												invoices.id,
												invoices.invoice_no,
												invoices.interest_status,
												invoices.in_finished_how,
												invoices.in_extra_note_crew,
												invoices.invoice_pdf_files,
                                                                                                invoices.interest_rate,
                                                                                                invoices.date_created,
												invoices.in_status,DATEDIFF(NOW(),invoices.date_created) as days,
                                                                                                invoices.overdue_date,
                                                                                                invoice_statuses.invoice_status_name";

            $sql_query .= "				FROM 		invoices
									 
									INNER JOIN 	estimates
									
									ON 			invoices.estimate_id= estimates.estimate_id 
									
									INNER JOIN 	leads
									
									ON 			estimates.lead_id= leads.lead_id 
									
									LEFT JOIN invoice_statuses
									
									ON 			invoices.in_status = invoice_statuses.invoice_status_id
									
									
									WHERE 1=1 ";


            if (isset($id) && $id != "") {
                $sql_query .= " AND estimates.estimate_id = '" . $id . "'";
            }
            $sql_query .= ' limit 1';

            $query = $this->db->query($sql_query);

            //if ($query->num_rows() > 0) {
            return $query->row();
            /*} else {
                return FALSE;
            }*/
        }
    }
    //*******************************************************************************************************************
//*************
//*************												update_interest	
//*************
//*******************************************************************************************************************	

    function update_interest($data, $id)
    {
        if ($data != '' && $id != '') {
            $this->db->where('id', $id);
            $this->db->limit(1);
            $update = $this->db->update($this->table, $data);
            //echo $this->db->last_query();
            if ($this->db->affected_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            echo "data not received";
        }
    }
    //*******************************************************************************************************************
//*************
//*************												getInvoiceOverdueRec function
//*************
//*******************************************************************************************************************	

    function getInvoiceOverdueRec()
    {
        $this->db->select('invoices.*, clients.*, clients_contacts.*, invoice_interest.rate, users.firstname, users.lastname, users.user_signature');
        $this->db->join('estimates', 'invoices.estimate_id = estimates.estimate_id');
        $this->db->join('users', 'users.id = estimates.user_id');
        $this->db->join('clients', 'clients.client_id = invoices.client_id');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left');
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
        $this->db->join('invoice_interest', 'invoice_interest.invoice_id = invoices.id', 'LEFT');
        $this->db->where('invoices.overdue_date <', date('Y-m-d'));
        $this->db->where('invoice_statuses.completed', 0);
        $this->db->where('is_hold_backs', 0);

        $query = $this->db->get($this->table);

        return $query->result();
    }
    //*******************************************************************************************************************
//*************
//*************												insert_interest	
//*************
//*******************************************************************************************************************	

    function insert_interest($data)
    {
        if ($data != '') {
            $update = $this->db->insert('invoice_interest', $data);
            //echo $this->db->last_query();
            if ($this->db->affected_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            echo "data not received";
        }
    }
    //*******************************************************************************************************************
//*************
//*************												getInterestData	
//*************
//*******************************************************************************************************************	

    function getInterestData($id)
    {
        if (!intval($id))
            return FALSE;

        $this->db->select('i.*, ii.*');
        $this->db->where('invoice_id', $id);
        //$this->db->where('nill_rate', '0');
        $this->db->join('invoices as i', 'i.id = ii.invoice_id', 'left');
        $this->db->order_by('ii.overdue_date');
        $query = $this->db->get('invoice_interest as ii');

        //if ($query->num_rows() > 0)
        return $query->result();

        //return FALSE;
    }
//*******************************************************************************************************************
//*************
//*************								update_invoice_interst function
//*************
//*******************************************************************************************************************	

    function update_invoice_interst($data, $wdata)
    {
        if ($data != '' && $wdata != '') {

            $this->db->where($wdata);
            $update = $this->db->update('invoice_interest', $data);
            //echo $this->db->last_query();
            if ($this->db->affected_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            echo "data not received";
        }
    }

    // End. update_estimates ();

    function update_invoice_balance($invoice_id)
    {
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_clients');
        $invoice = $this->find_by_id($invoice_id);
        $totalBalance = $this->mdl_estimates->get_total_estimate_balance($invoice->estimate_id);
        $this->db->where('id', $invoice_id);
        $this->db->update($this->table, array('invoice_balance' => $totalBalance));
    }

    function delete_invoice_new($id)
    {
        $sql = 'DELETE invoices, invoice_interest, payment_files FROM invoices ';
        $sql .= 'LEFT JOIN invoice_interest ON invoices.id = invoice_interest.invoice_id ';
        $sql .= 'LEFT JOIN payment_files ON invoices.id = payment_files.invoice_id ';
        $sql .= 'WHERE invoices.id = ' . intval($id);
        $this->db->query($sql);
        return true;
    }

    function get_invoices_stat($wdata = array())
    {
        $this->db->select("COUNT(invoices.id) as count_invoices , CONCAT( users.firstname,  ' ', users.lastname ) as username", FALSE);
        $this->db->join('status_log', "status_log.status_item_id = invoices.workorder_id AND status_value = 0 AND status_type = 'workorder'");
        $this->db->join('users', 'users.id = status_log.status_user_id');
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
        if (!empty($wdata))
            $this->db->where($wdata);
        $this->db->group_by('users.id');
        $this->db->order_by('count_invoices', 'DESC');
        $query = $this->db->get($this->table);
        return $query->result_array();

    }

    function find_by_id($id)
    {
        $this->load->model('mdl_estimates_orm');
        $clientPermissionsSubQuery = $this->mdl_clients->getPermissionsSubQuery();
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['invoices.id' => $id]);

        $this->db->select('invoices.*, invoice_statuses.*, totals.total_due, estimates.estimate_no, estimate_brand_id, estimates.status as estimate_status_id, estimates.estimate_hst_disabled, estimates.user_id, clients.*, estimate_statuses.est_status_name as status, estimate_statuses.est_status_id as status_id, leads.lead_id, leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip, leads.lead_no, clients_contacts.*, users.id as user_id, emailid, firstname, lastname, user_email, user_signature');
        $this->db->join('estimates', 'estimates.estimate_id=invoices.estimate_id', 'left');
        $this->db->join("($totalsSubQuery) totals", 'invoices.estimate_id=totals.estimate_id');
        $this->db->join('leads', 'estimates.lead_id=leads.lead_id', 'left');
        $this->db->join('clients', 'estimates.client_id=clients.client_id', 'left');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
        $this->db->join('estimate_statuses', 'estimate_statuses.est_status_id=estimates.status', 'left');
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
        $this->db->join('users', 'estimates.user_id=users.id', 'left');

        if(is_cl_permission_owner() && $clientPermissionsSubQuery) {
            $this->db->join('(' . $clientPermissionsSubQuery .') perm', 'perm.client_id = invoices.client_id', 'left');
            $this->db->where('perm.client_id IS NOT NULL');
        } elseif (is_cl_permission_none()) {
            $this->db->where('estimates.user_id', -1);
        }

        $this->db->where('invoices.id', $id);
        $query = $this->db->get('invoices');
        return $query->row();
    }

    function find_by_field($where)
    {
        $clientPermissionsSubQuery = $this->mdl_clients->getPermissionsSubQuery();

        $this->db->select('invoices.*, invoice_statuses.*, estimates.estimate_no, estimates.estimate_brand_id, estimates.status as estimate_status_id, estimates.estimate_hst_disabled, estimates.user_id, clients.*, estimate_statuses.est_status_name as status, estimate_statuses.est_status_id as status_id, leads.lead_id, leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip, leads.lead_no, clients_contacts.*, users.id as user_id, emailid, firstname, lastname, user_email, user_signature');
        $this->db->join('estimates', 'estimates.estimate_id=invoices.estimate_id', 'left');
        $this->db->join('leads', 'estimates.lead_id=leads.lead_id', 'left');
        $this->db->join('clients', 'estimates.client_id=clients.client_id', 'left');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
        $this->db->join('estimate_statuses', 'estimate_statuses.est_status_id=estimates.status', 'left');
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
        $this->db->join('users', 'estimates.user_id=users.id', 'left');
        $this->db->where($where);

        if(is_cl_permission_owner() && $clientPermissionsSubQuery) {
            $this->db->join('(' . $clientPermissionsSubQuery .') perm', 'perm.client_id = invoices.client_id', 'left');
            $this->db->where('perm.client_id IS NOT NULL');
        } elseif (is_cl_permission_none()) {
            $this->db->where('estimates.user_id', -1);
        }

        $query = $this->db->get('invoices');
        return $query->row();
    }

    function get_followup($statusList = [], $periodicity = NULL, $every = FALSE, $clientTypes = FALSE)
    {
        $followUpConfig = $this->config->item('followup_modules')['invoices'];
        $dbStatuses = [];
        foreach ($statusList as $value) {
			$dbStatuses[] = $value;
        }
        $this->db->select("DATEDIFF('" . date('Y-m-d') . "', IFNULL(FROM_UNIXTIME(MAX(status_log.status_date)), invoices.date_created)) as datediff, IFNULL(FROM_UNIXTIME(MAX(status_log.status_date)), invoices.date_created) as this_status_date, clients.client_id, users.id as estimator_id, clients_contacts.*, invoices.*, invoice_statuses.*", FALSE);
        $this->db->join('status_log', "status_item_id = estimate_id AND in_status = status_value AND status_type = 'invoice'", 'left');
        $this->db->join('estimates', 'invoices.estimate_id = estimates.estimate_id');
        $this->db->join('leads', 'estimates.lead_id = leads.lead_id');
        $this->db->join('clients', 'estimates.client_id = clients.client_id');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
        $this->db->join('client_tags', 'clients.client_id = client_tags.client_id', 'left');
        $this->db->join('followup_settings_tags', 'followup_settings_tags.tag_id = client_tags.tag_id', 'left');
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
        $this->db->join('users', 'users.id = estimates.user_id', 'left');
        $this->db->where_in('in_status', $dbStatuses);
       
        if ($clientTypes)
            $this->db->where_in('client_type', $clientTypes);
        if (!$every)
            $this->db->having("DATE_FORMAT(DATE_ADD(this_status_date, INTERVAL " . intval($periodicity) . " DAY), '%Y-%m-%d') = '" . date('Y-m-d') . "'");
        else{
            $this->db->having("(datediff % " . intval($periodicity) . ") = 0 AND datediff > 0");
		}
        /*$this->db->limit(1);*/
        $this->db->group_by('invoices.id');
        return $this->db->get($this->table)->result_array();
    }

    function get_followup_variables($id)
    {
        $this->load->model('mdl_estimates');
        $invoice = $this->find_by_id($id);
        $estimate = $this->mdl_estimates->find_by_id($invoice->estimate_id);
        $result['JOB_ADDRESS'] = $estimate->lead_address;
        $result['ADDRESS'] = $estimate->client_address;
        $result['EMAIL'] = $estimate->cc_email;
        $result['PHONE'] = $estimate->cc_phone;
        $result['NAME'] = $estimate->cc_name;
        $result['NO'] = $invoice->invoice_no;
        $result['LEAD_NO'] = $estimate->lead_no;
        $result['ESTIMATE_NO'] = $estimate->estimate_no;
        $result['INVOICE_NO'] = $invoice->invoice_no;
        $result['ESTIMATOR_NAME'] = $estimate->firstname . ' ' . $estimate->lastname;
        $totalForEstimate = $this->mdl_estimates->get_total_for_estimate($invoice->estimate_id);
        $result['AMOUNT'] = money($totalForEstimate['sum']);
        $result['TOTAL_DUE'] = money($this->mdl_estimates->get_total_estimate_balance($invoice->estimate_id));
        $result['CCLINK'] = '<a href="' . $this->config->item('payment_link') . 'payments/' . md5($invoice->invoice_no . $invoice->client_id) . '">link</a>';
        return $result;
    }

    function get_sum_interes($id = NULL, $where = [])
    {
        if (!$id && empty($where))
            return FALSE;
        $this->db->select("ROUND(SUM(interes_cost), 2) as sum", FALSE);
        $this->db->join('invoice_interest', 'invoice_interest.invoice_id = invoices.id', 'left');
        if ($id)
            $this->db->where('invoice_id', $id);
        else
            $this->db->where($where);
        return $this->db->get($this->table)->row_array();
    }

    function get_invoice_interes($wdata = [])
    {
        $this->db->select('invoices.estimate_id, invoice_interest.invoice_id');
        if (!empty($wdata)) {
            $this->db->where($wdata);
        }
        $this->db->join('invoices', 'invoices.id = invoice_interest.invoice_id');
        $this->db->group_by('invoice_interest.invoice_id');
        return $this->db->get('invoice_interest')->result();
    }

    function update_all_invoice_interes($estimate_id)
    {
        if (!$estimate_id)
            return FALSE;
        $this->load->model('mdl_estimates_orm');
        $this->load->model('mdl_clients');
        $invoice = $this->getEstimatedData($estimate_id);
        $estimate = $this->mdl_estimates_orm->get($estimate_id);
        $payments = $this->mdl_clients->get_payments(['estimates.estimate_id' => $estimate_id]);

        if (!empty($invoice) && isset($invoice->id)) {
            $client = $this->mdl_clients->find_by_id($invoice->client_id);
            $term = \application\modules\invoices\models\Invoice::getInvoiceTerm($client->client_type);
            $interes = $this->getInterestData($invoice->id);
            if ($interes && !empty($interes)) {
                $newBal = ($estimate->sum_taxable + $estimate->sum_non_taxable) - $estimate->discount_total;
                $intCost = 0;
                foreach ($interes as $k => $v) {
                    if (isset($payments) && !empty($payments)) {
                        foreach ($payments as $jk => $pay) {
                            if ($pay['payment_date'] < (strtotime($v->overdue_date) - $term * 86400)) {
                                $newBal = $newBal - $pay['payment_amount'];
                                unset($payments[$jk]);
                            }
                        }
                    }
                    $interest = abs($v->rate / 100);
                    $intCost = round($newBal * $interest, 2);
                    $intCost = $intCost >= 0 ? $intCost : 0;
                    $newBal += $intCost;
                    $this->update_invoice_interst(['interes_cost' => $intCost], ['id' => $v->id]);
                }
                $interes = (array)$this->getInterestData($invoice->id);
                return $interes;
            }
        }
        return TRUE;
    }

    function invoice_overdue_sum($where = [], $sum = FALSE)
    {
        if ($sum)
            $this->db->select("SUM(invoice_interest.interes_cost) as sum", FALSE);

        $this->db->join('invoice_interest', 'invoices.id= invoice_interest.invoice_id');
        $this->db->join('status_log', 'invoices.id = status_log.status_item_id');

        $this->db->join('invoice_statuses is1', 'status_log.status_value=is1.invoice_status_id AND is1.completed = 1');
        $this->db->join('invoice_statuses is2', 'invoices.in_status=is2.invoice_status_id');

        if (!empty($where))
            $this->db->where($where);
        $this->db->where(['interest_status' => 'No', 'is2.completed' => 1]);

        if ($sum) {
            return $this->db->get($this->table)->row();
        }

        $this->db->group_by('invoices.id');
        return $this->db->get($this->table)->result();
    }

    function get_last_invoice_status($id, $paid = FALSE)
    {
        $this->db->select('status_log.status_value');
        $this->db->where(["status_log.status_type" => 'invoice', "status_log.status_item_id" => $id]);
        if ($paid) {
            $this->db->join('invoice_statuses', 'status_log.status_value=invoice_statuses.invoice_status_id AND invoice_statuses.completed = 0');
            //$this->db->where("status_value != 4");
        }
        $this->db->order_by("status_log.status_id", "DESC");
        $this->db->limit(1);
        $query = $this->db->get('status_log');
        return $query->row_array();
    }

    function getStartInvoicesReportDate()
    {
        $this->db->select_min('date_created');
        $row = $this->db->get('invoices')->row();

        $date = date('Y-01-01');
        if ($row && $row->date_created) {
            $date = date('Y-01-01', strtotime($row->date_created));
        }

        return $date;
    }

    function getEndInvoicesReportDate()
    {
        $this->db->select_max('date_created');
        $row = $this->db->get('invoices')->row();
        return $row->date_created;
    }

    public function find_all($wdata = array(), $order = '', $join = array())
    {
        if (!empty($wdata)) {
            $this->db->where($wdata);
        }
        if ($order) {
            $this->db->order_by($order);
        }

        if(isset($join[0]) && isset($join[1])){
            $type = element(2, $join, 'inner');
            $this->db->join($join[0], $join[1], $type);
        }
        return $this->db->get($this->table)->result();
    }

    public function getQuickbooksData($wdata = [], $limit = NULL, $offset = 0) {

        $this->db->select(
            'invoices.id, invoices.estimate_id, invoices.invoice_no, invoices.invoice_notes, ' .
            'invoices.overdue_date, invoices.date_created, estimates.estimate_hst_disabled, discounts.discount_id, ' .
            'discounts.discount_amount, discounts.discount_date, discounts.discount_percents, clients.client_qb_id,' .
            'leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip'
        );

        if (!empty($wdata)) {
            $this->db->where($wdata);
        }

        $joinTables = [
            ['discounts', 'discounts.estimate_id = invoices.estimate_id', 'left'],
            ['estimates','estimates.estimate_id = invoices.estimate_id', 'inner'],
            ['clients', 'clients.client_id = invoices.client_id', 'inner'],
            ['leads', 'leads.lead_id = estimates.lead_id', 'inner'],

        ];

        foreach ($joinTables as $join)
            $this->db->join($join[0], $join[1], $join[2] ?? 'inner');

        if ($limit !== NULL)
            $this->db->limit($limit, $offset);

        return $this->db->get($this->table)->result();
    }
    
    function get_new_invoices_stat($where = [])
    {
		$this->load->model('mdl_estimates_orm'); 
		$calcQuery = $this->mdl_estimates_orm->calcQuery($where);
		$this->db->_reset_select();
		
		$this->db->select("SUM(IFNULL(`totals`.total_confirmed, 0)) as sales, COUNT(DISTINCT(totals.estimate_id)) as quantity");
		
		$this->db->join("($calcQuery) totals", "totals.estimate_id = estimates.estimate_id");
		$this->db->join("invoices", "estimates.estimate_id = invoices.estimate_id",'left');
		if($where && count($where))
			$this->db->where($where);
		$query = $this->db->get('estimates');
		return $query->row_array();
			
	}
}

?>
