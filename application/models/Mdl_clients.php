<?php

class Mdl_clients extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->table = 'clients';
        $this->table1 = 'client_papers';
        $this->primary_key = 'clients.client_id';
    }


    public function find_all_with_limit($search_array = array(), $limit = '', $start = '', $order = '', $wdata = array(), $group_by = '')
    {
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left');
//        $this->db->join('client_tags', 'clients.client_id = client_tags.client_id', 'left');
//        $this->db->join('tags', 'tags.tag_id = client_tags.tag_id', 'left');

        if (!empty($wdata) && $wdata)
            $this->db->where($wdata);
        if ($limit != '') {
            $this->db->limit($limit, $start);
        }

        if (!empty($search_array)) {
            $this->db->or_like($search_array);
        }

        if ($order) {
            $this->db->order_by($order);
        }
        $this->db->group_by('client_id');
        if ($group_by != '')
            $this->db->group_by($group_by);

        return $this->db->get($this->table)->result();
    }

    public function record_count($or_wdata = array(), $wdata = array())
    {
        $this->db->select('COUNT(DISTINCT client_id) numrows', FALSE);
        $this->db->from($this->table);
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id', 'left');

        if (!empty($or_wdata)) {
            $this->db->or_like($or_wdata);
        }

        if (!empty($wdata)) {
            $this->db->where($wdata);
        }
        $result = $this->db->get()->row();
        return $result->numrows ?? 0;
    }

//*******************************************************************************************************************
//*************
//*************            								get_notes($id);
//*************
//*******************************************************************************************************************
//Request to get all notes for the clint's profile
    public function get_notes($id, $type = NULL, $wdata = array(), $limit = 200, $offset = 0)
    {
        if ($type == 'all')
            $type = NULL;
        $this->db->select('client_notes.client_note_id, client_notes.client_note_top, client_notes.client_id, client_notes.client_note_date, client_notes.client_note, client_notes.author, client_notes.robot, users.id, users.firstname, users.lastname, users.picture, users.rate');
        $this->db->join('users', 'client_notes.author = users.id');
        $this->db->where('client_notes.client_id', $id);
        if (!empty($wdata)) {
            foreach ($wdata as $key => $val)
                $this->db->where($key, $val);
        }
        $this->db->where('client_notes.client_id', $id);
        if ($type)
            $this->db->where('client_notes.client_note_type', $type);
        /*else
            $this->db->where("client_notes.client_note_type != 'contact'");*/
        $this->db->order_by('client_note_top', 'DESC');
        $this->db->order_by('client_note_date', 'DESC');
        $this->db->order_by('client_note_id', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('client_notes');

        return $query->result_array();
    }// end get_notes;

    function get_notes_app($id, $type = NULL, $wdata = array(), $limit = 200, $offset = 0){
        if ($type == 'all')
            $type = NULL;
        $this->db->select('client_notes.client_note_id, client_notes.client_note_date, client_notes.client_note, client_notes.author');
        $this->db->join('users', 'client_notes.author = users.id');
        $this->db->where('client_notes.client_id', $id);
        if (count($wdata)) {
            foreach ($wdata as $key => $val)
                $this->db->where($key, $val);
        }
        $this->db->where('client_notes.client_id', $id);
        if ($type)
            $this->db->where('client_notes.client_note_type', $type);
        else
            $this->db->where("client_notes.client_note_type != 'contact'");
        $this->db->order_by('client_note_top', 'DESC');
        $this->db->order_by('client_note_date', 'DESC');
        $this->db->order_by('client_note_id', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('client_notes');

        return $query->result_array();
    }

//*******************************************************************************************************************
//*************
//*************            								delete_notes($id);
//*************
//*******************************************************************************************************************

    function delete_note_client($client_note_id)
    {
        if ($client_note_id) {

            return $row = $this->db->where('client_note_id', $client_note_id)->limit(1)->get('client_notes')->row();

        }
    }


    function delete_note($client_note_id)
    {
        if ($client_note_id) {

            $this->db->where('client_note_id', $client_note_id);
            $this->db->delete('client_notes');

            if ($this->db->affected_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    function update_note($client_note_id, $data)
    {
        if ($client_note_id) {

            $this->db->where('client_note_id', $client_note_id);
            $this->db->update('client_notes', $data);

            if ($this->db->affected_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return FALSE;
    }

    function update_note_by($wdata, $data)
    {
        $this->db->where($wdata);
        if ($this->db->update('client_notes', $data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

//*******************************************************************************************************************
//*************
//*************            								update_client(*.*);
//*************
//*******************************************************************************************************************
    //Update client profile.
    public function update_client($update_data, $wdata)
    {

        $this->db->where($wdata);
        if ($this->db->update('clients', $update_data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }//end of update_client;


    //*******************************************************************************************************************
    //*************
    //*************           Add a new client model
    //*************
    //*******************************************************************************************************************
    public function add_new_client()
    {
        //Array for MySql update;
        //Set note date to avoide escaped values;
        $client_date_created = date('Y-m-d');
        //Set client reference person name
        $client_referred_by = '';
        if ($this->input->post('client_referred_by') != '') {
            $client_referred_by = $this->input->post('client_referred_by');
        }

        $client_address_check = '0';
        $new_client_main_intersection2 = $new_client_address2 = '';
        $new_client_city2 = $new_client_state2 = $new_client_zip2 = '';

        if ($this->input->post('new_add') == 1) {
            $client_address_check = strip_tags($this->input->post('client_address_check'));
            $new_client_main_intersection2 = strip_tags($this->input->post('new_client_main_intersection2'));
            $new_client_address2 = strip_tags($this->input->post('new_client_address2'));
            $new_client_city2 = strip_tags($this->input->post('new_client_city2'));
            $new_client_state2 = strip_tags($this->input->post('new_client_state2'));
            $new_client_zip2 = strip_tags($this->input->post('new_client_zip2'));
        }
        
        if(isset($this->session->userdata['user_id'])){
            $maker = $this->session->userdata['user_id'];
        } else {
            $maker = $this->user->id;
        }

        //Array with values;
        $data = array(
            'client_brand_id' => (int)$this->input->post('client_brand_id'),
            'client_name' => strip_tags($this->input->post('new_client_name')),
            'client_maker' => $maker,
            'client_contact' => strip_tags($this->input->post('new_client_contact')),
            'client_type' => strip_tags($this->input->post('new_client_type')),
            'client_source' => strip_tags($this->input->post('new_client_source')),
            'client_referred_by' => strip_tags($this->input->post('client_referred_by')),
            'client_date_created' => $client_date_created,
            'client_main_intersection' => strip_tags($this->input->post('new_client_main_intersection')),
            'client_address' => strip_tags($this->input->post('new_client_address')),
            'client_city' => strip_tags($this->input->post('new_client_city')),
            'client_state' => strip_tags($this->input->post('new_client_state')),
            'client_zip' => strip_tags($this->input->post('new_client_zip')),
            'client_country' => strip_tags($this->input->post('new_client_country')),
            'client_lat' => strip_tags($this->input->post('new_client_lat')),
            'client_lng' => strip_tags($this->input->post('new_client_lon')),
            'client_address_check' => $client_address_check,
            'client_main_intersection2' => $new_client_main_intersection2,
            'client_address2' => $new_client_address2,
            'client_city2' => $new_client_city2,
            'client_state2' => $new_client_state2,
            'client_zip2' => $new_client_zip2
        );

        if (!empty($this->input->post('new_client_tax_name'))) {
            $data['client_tax_name'] = strip_tags($this->input->post('new_client_tax_name'));
            $data['client_tax_rate'] = floatval($this->input->post('new_client_tax_rate'));
            $data['client_tax_value'] = floatval($this->input->post('new_client_tax_value'));
        }

        /*********костыль для APP 1.10.0************/
        if(!$data['client_lng'])
            $data['client_lng'] = strip_tags($this->input->post('new_client_lng'));
        /*********************/

        if ($this->db->insert('clients', $data) == FALSE)
            return FALSE;
        //there was a problem creating a client;

        return $this->db->insert_id();
    }

    function add_client_contact($data)
    {
        $this->db->insert('clients_contacts', $data);
        return TRUE;
    }

    function update_client_contact($data, $wdata = [])
    {
        if (empty($wdata) || !$wdata)
            return FALSE;

        $this->db->where($wdata);
        $this->db->update('clients_contacts', $data);
        return TRUE;
    }

    function delete_client_contact($cc_id = NULL)
    {
        if (!$cc_id)
            return FALSE;
        $this->db->where('cc_id', $cc_id);
        $this->db->delete('clients_contacts');
        return TRUE;
    }

    function get_client_contacts($wdata = [])
    {
        $this->db->where($wdata);
        $query = $this->db->get('clients_contacts');
        return $query->result_array();
    }

    function get_client_tags($wdata = [])
    {
        $this->db->select('tags.tag_id, tags.name');
        $this->db->where($wdata);
        $this->db->join('tags', 'tags.tag_id = client_tags.tag_id');

        $query = $this->db->get('client_tags');
        return $query->result_array();
    }

    function get_client_contacts_app($wdata = [])
    {
        $this->db->select('cc_title, cc_id, cc_name, cc_phone, cc_email');
        $this->db->where($wdata);
        $query = $this->db->get('clients_contacts');
        return $query->result_array();
    }

    function get_client_contact($wdata = [])
    {
        $this->db->where($wdata);
        $query = $this->db->get('clients_contacts');
        return $query->row_array();
    }

    function get_primary_client_contact($client_id = NULL)
    {
        $this->db->where('cc_print', 1);
        $this->db->where('cc_client_id', $client_id);
        $query = $this->db->get('clients_contacts');
        if(!$query->num_rows()) {
            $this->db->where('cc_client_id', $client_id);
            $this->db->limit(1);
            $query = $this->db->get('clients_contacts');
        }
        return $query->row_array();
    }

    function add_new_client_with_data($data)
    {
        if ($this->db->insert('clients', $data) == FALSE) {
            return FALSE;
        } else {
            return $this->db->insert_id();
        }
    }

// end add_new_client;
//*******************************************************************************************************************
//*******************************************************************************************************************
// Get new client ID after registration: NOT necessary(abilash)

    public function get_new_client_id()
    {

        $search = array(
            'client_name' => strip_tags($this->input->post('new_client_name')),
            'cc_phone' => strip_tags($this->input->post('new_client_phone'))
        );
        $this->db->join('clients_contacts', 'clients.client_id=clients_contacts.cc_client_id', 'left');

        return $row = $this->db->get_where('clients', $search)->row();
    }

    /*
     * function get_user
     *
     * param select, wheredata, ...
     * returns rows or false
     *
     */

    function get_clients($select = '', $wdata = '')
    {
        if ($select != '') {
            $this->db->select($select);
        }
        if ($wdata != '') {
            $this->db->where($wdata);
        }
        $query = $this->db->get($this->table);
        //if ($query->num_rows() > 0) {
        return $query;
        /*} else {
            return FALSE;
        }*/
    }

    public function search_clients()
    {

        $this->db->select('*');
        $search = strip_tags($this->input->post('search_value'));
        $array = array(
            'client_name' => $search,
            'client_contact' => $search,
            'cc_phone' => $search
        );
        $this->db->or_like($array);
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left');
        return $row = $this->db->get('clients');
    }

    function get_payments($wdata = array(), $limit = '', $start = '', $order = 'client_payments.payment_type') : array
    {
        $this->db->where('payment_amount <>', 0);
        $this->db->join('estimates', 'estimates.estimate_id = client_payments.estimate_id');
        $this->db->join('clients', 'clients.client_id = estimates.client_id', 'left');
        $this->db->join('users', 'users.id = client_payments.payment_author', 'left');
        $this->db->join('payment_account', 'payment_account.payment_account_id = client_payments.payment_account', 'left');
        $this->db->join('leads', 'estimates.lead_id = leads.lead_id');
        if ($limit != '') {
            $this->db->limit($limit, $start);
        }
        if (!empty($wdata))
            $this->db->where($wdata);
        if ($order == '')
            $order = 'payment_date DESC';
        $this->db->order_by($order);
        return $this->db->get('client_payments')->result_array();
    }

    function get_payments_sum($wdata = array())
    {
        if (!empty($wdata))
            $this->db->where($wdata);
        $this->db->select_sum('payment_amount');
        $row = $this->db->get('client_payments')->row_array();
        $sum = $row['payment_amount'] ? $row['payment_amount'] : 0;
        return round($sum, 2);
    }

    function update_payment($payment_id, $data)
    {
        $this->db->where('payment_id', $payment_id);
        $this->db->update('client_payments', $data);
        return TRUE;
    }

    function insert_payment($data)
    {
        $this->db->insert('client_payments', $data);
        $id = $this->db->insert_id();
        return $id;
    }

    function delete_payment($payment_id)
    {
        $this->db->where('payment_id', $payment_id);
        $this->db->delete('client_payments');
        return TRUE;
    }

    function get_discount($wdata = array())
    {
        $this->db->join('estimates', 'estimates.estimate_id = discounts.estimate_id');
        if (!empty($wdata))
            $this->db->where($wdata);
        return $this->db->get('discounts')->row_array();
    }

    function update_discount($payment_id, $data)
    {
        $this->db->where('discount_id', $payment_id);
        $this->db->update('discounts', $data);
        return TRUE;
    }

    function insert_discount($data)
    {
        $this->db->insert('discounts', $data);
        return $this->db->insert_id();
    }

    function check_contact($phone = '', $email = null)
    {
        if (!$phone && !$email)
            return false;
        if ($phone) {
            preg_match('/([0-9]{3}).*?([0-9]{3}).*?([0-9]{1,4})/is', $phone, $result);
            if (!empty($result)) {
                $like = isset($result[1]) ? '%' . $result[1] : '';
                $like .= isset($result[2]) ? '%' . $result[2] : '';
                $like .= isset($result[3]) ? '%' . $result[3] : '';
                $like .= $like ? '%' : '';
                if (strlen($like) < 5)
                    return FALSE;
            }
        }
        $this->db->select('client_id, client_name');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id', 'left');
        if (isset($like) && $like) {
            $this->db->where("cc_phone LIKE '" . $like . "'");
            if ($email)
                $this->db->or_where("cc_email", $email);
        } else
            $this->db->where("cc_email", $email);
        $this->db->group_by('clients.client_id');
        $this->db->limit(10);
        $query = $this->db->get('clients')->result_array();
        return $query;
    }

    function check_address($street = '', $city = null)
    {
        $this->db->select('client_id, client_name');
        if (isset($street) && $street) {
            $this->db->where("client_address", $street);
            if ($city)
                $this->db->where("client_city", $city);
        } else
            $this->db->where("client_city", $city);
        $query = $this->db->get('clients')->result_array();

        return $query;
    }

    function complete_client_removal($client_id)
    {
        if (!$client_id)
            return FALSE;

        $this->load->model('mdl_estimates');
        $this->load->helper('estimates_helper');

        /*********DELETE PAYMENTS FILES*****************/
        $paymentsPath = 'uploads/payment_files/' . $client_id . '/';
        if (isset($client_id) && is_dir($paymentsPath))
            recursive_rm_files($paymentsPath);
        /*********DELETE PAYMENTS FILES*****************/

        /*********DELETE NOTES FILES*****************/
        $notesPath = 'uploads/notes_files/' . $client_id . '/';
        if (isset($client_id) && is_dir($notesPath))
            recursive_rm_files($notesPath);
        /*********DELETE NOTES FILES*****************/

        /*********DELETE WORKORDERS FILES*****************/
        $workordersPath = 'uploads/workorder_files/' . $client_id . '/';
        if (isset($client_id) && is_dir($workordersPath))
            recursive_rm_files($workordersPath);
        /*********DELETE WORKORDERS FILES*****************/

        /*********DELETE WORKORDERS FILES*****************/
        $clientsPath = 'uploads/clients_files/' . $client_id . '/';
        if (isset($client_id) && is_dir($clientsPath))
            recursive_rm_files($clientsPath);
        /*********DELETE WORKORDERS FILES*****************/

        $estimates = $this->mdl_estimates->get_client_estimates($client_id);
        if ($estimates) {
            foreach ($estimates->result() as $estimate) {
                /******DELETE ESTIMATES FILES*****/
                $estimatePath = 'uploads/estimate_photos/' . ceil(intval($estimate->estimate_no) / 1000) . '/' . $estimate->estimate_no . '/';
                if (isset($client_id) && is_dir($estimatePath))
                    recursive_rm_files($estimatePath);
                /******DELETE ESTIMATES FILES*****/
            }
        }

        $this->delete_all_client_papers(intval($client_id));
        /***************DELETE ALL RECORDS FROM DB FOR THIS CLIENT***********************/

        $this->db->where('client_id', intval($client_id));
        $this->db->join('client_notes', 'client_notes.client_id = clients.client_id', 'left');
        $this->db->delete(array('clients', 'client_notes'));

        /*
        $sql = 'DELETE clients, client_notes FROM clients ';
        $sql .= 'LEFT JOIN client_notes ON clients.client_id = client_notes.client_id ';
        $sql .= 'WHERE clients.client_id = ' . intval($client_id);
        $this->db->query($sql);
        */

        $this->db->where('cc_client_id', intval($client_id));
        $this->db->delete('clients_contacts');

        $this->db->where('task_client_id', intval($client_id));
        $this->db->delete('client_tasks');
        /*
        $sql = 'DELETE client_tasks FROM client_tasks WHERE task_client_id = ' . intval($client_id);
        $this->db->query($sql);
        */


        /*???????
        $this->db->where('estimates.client_id', intval($client_id));
        $this->db->join('client_payments', 'client_payments.estimate_id = estimates.estimate_id', 'left');
        $this->db->join('discounts', 'discounts.estimate_id = estimates.estimate_id', 'left');
        $this->db->join('project', 'project.estimate_id = estimates.estimate_id', 'left');
        $this->db->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id', 'left');
        $this->db->join('estimates_services_crews', 'estimates_services_crews.crew_estimate_id = estimates.estimate_id', 'left');
        $this->db->join('estimates_services_equipments', 'estimates_services_equipments.equipment_estimate_id = estimates.estimate_id', 'left');
        $this->db->delete(array('estimates', 'client_payments', 'discounts', 'project', 'estimates_services', 'estimates_services_crews', 'estimates_services_equipments'));
        */
        $sql = 'DELETE estimates, client_payments, discounts, project, estimates_services, estimates_services_crews, estimates_services_equipments FROM estimates ';
        $sql .= 'LEFT JOIN client_payments ON estimates.estimate_id = client_payments.estimate_id ';
        $sql .= 'LEFT JOIN discounts ON estimates.estimate_id = discounts.estimate_id ';
        $sql .= 'LEFT JOIN project ON estimates.estimate_id = project.estimate_id ';
        $sql .= 'LEFT JOIN estimates_services ON estimates.estimate_id = estimates_services.estimate_id ';
        $sql .= 'LEFT JOIN estimates_services_crews ON estimates.estimate_id = estimates_services_crews.crew_estimate_id ';
        $sql .= 'LEFT JOIN estimates_services_equipments ON estimates.estimate_id = estimates_services_equipments.equipment_estimate_id ';
        $sql .= 'WHERE estimates.client_id = ' . intval($client_id);
        $this->db->query($sql);

        $sql = 'DELETE invoices, invoice_interest, payment_files FROM invoices ';
        $sql .= 'LEFT JOIN invoice_interest ON invoices.id = invoice_interest.invoice_id ';
        $sql .= 'LEFT JOIN payment_files ON invoices.id = payment_files.invoice_id ';
        $sql .= 'WHERE invoices.client_id = ' . intval($client_id);
        $this->db->query($sql);

        $this->db->where('client_id', intval($client_id));
        $this->db->join('payments', 'payments.client_id = leads.client_id', 'left');
        $this->db->join('workorders', 'workorders.client_id = leads.client_id', 'left');
        $this->db->join('schedule', 'schedule.event_wo_id = workorders.id', 'left');
        $this->db->delete(array('leads', 'payments', 'workorders'));

        /*
        $sql = 'DELETE leads, payments, workorders FROM leads ';
        $sql .= 'LEFT JOIN payments ON leads.client_id = payments.client_id ';
        $sql .= 'LEFT JOIN workorders ON leads.client_id = workorders.client_id ';
        $sql .= 'WHERE leads.client_id = ' . intval($client_id);
        $this->db->query($sql);
        */
        /***************DELETE ALL RECORDS FROM DB FOR THIS CLIENT***********************/

        return TRUE;
    }

    function stat_calls($where = array())
    {
        $this->db->select('COUNT( WEEKDAY( client_note_date ) ) AS count, DAYNAME( client_note_date ) AS weekday');
        $this->db->where('client_note_type', 'contact');
        if (!empty($where))
            $this->db->where($where);
        $this->db->group_by('weekday');
        $this->db->order_by('count', 'DESC');
        $query = $this->db->get('client_notes');
        return $query->result_array();
    }

    function stat_payments($where = array())
    {
        $this->db->select('COUNT( WEEKDAY( FROM_UNIXTIME ( payment_date ) ) ) AS count, DAYNAME( FROM_UNIXTIME ( payment_date ) ) AS weekday');
        if (!empty($where))
            $this->db->where($where);
        $this->db->group_by('weekday');
        $this->db->order_by('count', 'DESC');
        $query = $this->db->get('client_payments');
        return $query->result_array();
    }

    function stat_clients($where = array())
    {
        $this->db->select('COUNT( WEEKDAY ( client_date_created ) ) AS count, DAYNAME( client_date_created ) AS weekday');
        if (!empty($where))
            $this->db->where($where);
        $this->db->group_by('weekday');
        $this->db->order_by('count', 'DESC');
        $query = $this->db->get('clients');
        return $query->result_array();
    }

    function stat_payments_by_day($where = array())
    {
        $this->db->select("SUM( payment_amount ) AS sum, FROM_UNIXTIME( payment_date, '%Y-%m-%d' ) AS payment_date_new", FALSE);
        if (!empty($where))
            $this->db->where($where);
        $this->db->group_by('payment_date_new');
        $query = $this->db->get('client_payments');
        $result = $query->result_array();
        $sum = 0;
        $count = is_array($result) && !empty($result) ? count($result) : 1;//countOk
        foreach ($result as $row)
            $sum += $row['sum'];
        return (round($sum / $count));
    }

    function stat_payments_by_month($where = array())
    {
        $this->db->select("SUM( payment_amount ) AS sum, FROM_UNIXTIME( payment_date, '%Y-%m' ) AS payment_date_new", FALSE);
        if (!empty($where))
            $this->db->where($where);
        $this->db->group_by('payment_date_new');
        $query = $this->db->get('client_payments');
        $result = $query->result_array();
        $sum = 0;
        $count = is_array($result) && !empty($result) ? count($result) : 1;//countOk
        foreach ($result as $row)
            $sum += $row['sum'];
        return (round($sum / $count));
    }

    function get_clients_with_coords()
    {
        $this->db->select('clients.*, clients_contacts.*, leads.latitude, leads.longitude');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left');
        $this->db->join('leads', 'leads.client_id = clients.client_id');
        $this->db->group_by('clients.client_id');
        return $this->db->get($this->table)->result_array();
    }

    function count_payments($wdata = array())
    {
        $this->db->where('payment_amount <>', 0);
        if (!empty($wdata))
            $this->db->where($wdata);
        $this->db->join('estimates', 'estimates.estimate_id = client_payments.estimate_id');
        return $this->db->count_all_results('client_payments');
    }

    function payment_account($where = array())
    {
        if (!empty($where))
            $this->db->where($where);
        return $this->db->get('payment_account')->result_array();
    }

    function payment_made($where = array())
    {
        $sql_query = 'SELECT payment_account, SUM(payment_amount) as sum FROM client_payments WHERE payment_account is NOT NULL';
        if (!empty($where)) {
            foreach ($where as $key => $val)
                $sql_query .= ' AND ' . $key . '="' . $val . '"';
        }
        $sql_query .= ' GROUP BY payment_account';
        //SELECT payment_account, SUM(payment_amount) as 'sum' FROM `client_payments` WHERE payment_checked = 1 and payment_account is not null group by payment_account
        $query = $this->db->query($sql_query);
        //if ($query->num_rows() > 0)
        return $query->result_array();
        /*else
            return FALSE;*/
    }

    function find_by_phone($phone)
    {
        $result = array();
        if (strlen($phone) > strlen(ltrim($phone, '+')))
            $phone = substr(ltrim($phone, '+'), 1);

        if (isset($phone[0]) && $phone[0] == '1')
            $phone = ltrim($phone, '1');

        $numbers = preg_match_all('/([0-9])/is', $phone, $matches);

        if (isset($matches[1]) && count($matches[1]) > 5) {//countOk
            $like = '%';
            foreach ($matches[1] as $key => $val)
                $like .= "$val%";

            if (strlen($like) > 1)
                $result = $this->db->query("SELECT * FROM clients JOIN clients_contacts ON clients.client_id=clients_contacts.cc_client_id WHERE cc_phone LIKE '" . $like . "'")->row_array();
        }

        return $result;
    }

    function get_clients_coords()
    {
        $this->db->where('leads.latitude IS NOT NULL');
        $this->db->where('leads.longitude IS NOT NULL');
        $this->db->select('leads.latitude, leads.longitude');
        $this->db->join('leads', 'leads.client_id = clients.client_id');
        $this->db->join('invoices', 'invoices.client_id = clients.client_id');
        $this->db->group_by('clients.client_id');
        return $this->db->get($this->table)->result_array();
    }

    function get_client_by_id($id)
    {
        $this->db->select('estimates.*, users.*, clients.*, clients_contacts.*');
        $this->db->join('clients_contacts', 'clients_contacts.cc_client_id = clients.client_id AND clients_contacts.cc_print = 1', 'left');
        $this->db->join('estimates', 'estimates.client_id = clients.client_id', 'left');
        $this->db->join('users', 'users.id = estimates.user_id', 'left');
        return $this->db->where(array('clients.client_id' => $id))->limit(1)->get('clients')->row();

    }

    function get_client_by_fields($fields = [])
    {
        if(empty($fields))
            return false;

        $this->db->select('estimates.*, clients.*, clients_contacts.*');
        $this->db->join('clients_contacts', 'clients_contacts.cc_client_id = clients.client_id', 'left');
        $this->db->join('estimates', 'estimates.client_id = clients.client_id', 'left');
        return $this->db->where($fields)->limit(1)->get('clients')->row();

    }
    
    function get_client_app($id)
    {
        $this->db->select('clients.client_id, clients.client_brand_id, clients.client_type, clients.client_name, clients.client_lat, clients.client_lng, clients.client_address, clients.client_city, '.
                          'clients.client_state, clients.client_zip, clients.client_country, clients.client_tax_name, clients.client_tax_rate, clients.client_tax_value, clients_contacts.cc_id as primary_contact_id, estimates.estimate_planned_total, estimates.estimate_balance, clients.client_main_intersection');
        $this->db->join('clients_contacts', 'clients_contacts.cc_client_id = clients.client_id AND clients_contacts.cc_print = 1', 'left');
        $this->db->join('estimates', 'estimates.client_id = clients.client_id', 'left');
        
        return $this->db->where(array('clients.client_id' => $id))->limit(1)->get('clients')->row();
    }

    // TODO: Check for unused functionality
    function search_reff($reff)
    {

        $this->db->select("clients.client_id as id, clients.client_brand_id, CONCAT(clients_contacts.cc_name, ', ', IFNULL(estimates.estimate_no, 'No Estimates'), ' - ', clients.client_address, ', ', clients.client_city) as text", FALSE);
        $this->db->join('estimates', 'estimates.client_id = clients.client_id', 'left');
        $this->db->join('clients_contacts', 'clients_contacts.cc_client_id = clients.client_id AND cc_print = 1', 'left');
        $array = array(
            'client_name' => $reff,
            'estimate_no' => $reff,
            'client_address' => $reff,
        );
        $this->db->or_like($array);
        //	$this->db->group_by('clients.client_id');
        return $this->db->get('clients');
    }


    public function get_reff_by_id($clientId)
    {
        $this->db->select("clients.client_id as id, clients.client_brand_id, CONCAT(clients_contacts.cc_name, ', ', IFNULL(estimates.estimate_no, 'No Estimates'), ' - ', clients.client_address, ', ', clients.client_city) as text", FALSE);
        $this->db->join('estimates', 'estimates.client_id = clients.client_id', 'left');
        $this->db->join('clients_contacts', 'clients_contacts.cc_client_id = clients.client_id AND cc_print = 1', 'left');

        $this->db->where([
            'clients.client_id =' => $clientId
        ]);

        $this->db->group_by('clients.client_id');

        $result = $this->db->get('clients')->result();

        return empty($result) ? null : $result[0];
    }
    
    function client_search_app($search_keyword) {
        $this->db->select("clients.client_address, clients.client_brand_id, clients.client_name, clients_contacts.cc_phone, clients_contacts.cc_email, clients.client_id", FALSE);			
        $this->db->from('clients');
        $this->db->join('clients_contacts', 'cc_client_id = clients.client_id AND cc_print = 1', 'left');        
        $this->db->like('clients.client_name', $search_keyword);
        $this->db->or_like('clients.client_address', $search_keyword);
        $this->db->or_like('clients_contacts.cc_phone', $search_keyword);
        $this->db->or_like('clients_contacts.cc_name', $search_keyword);
        $this->db->or_like('clients_contacts.cc_email', $search_keyword);
        $this->db->or_like('clients.client_city', $search_keyword);
        $this->db->order_by('clients.client_date_created', 'desc');
        $this->db->limit(100);
        return $this->db->get()->result();
    }

    function get_count_letters_to_stats($where = array())
    {
        $this->db->select("COUNT(client_note_id) AS count_letters, CONCAT(firstname, ' ', lastname) AS username", FALSE);
        $this->db->join('users', 'users.id = client_notes.author');
        if (!empty($where))
            $this->db->where($where);
        $this->db->where("(client_note = 'Sent to client'
		OR client_note LIKE '%Sent PDF of estimate%'
		OR client_note LIKE '%Sent tnx letter to client%'
		OR client_note LIKE '%Sent PDF of invoice%')");
        $this->db->group_by('author');
        $this->db->order_by('count_letters', 'DESC');
        $query = $this->db->get('client_notes');
        return $query->result_array();

    }

    function get_count_clients_to_stats($where = array())
    {
        $this->db->select("COUNT(client_id) as count_clients, CONCAT(users.firstname, ' ', users.lastname) as username", FALSE);
        $this->db->join('users', 'users.id = clients.client_maker');
        if (!empty($where))
            $this->db->where($where);
        $this->db->group_by('client_maker');
        $this->db->order_by('count_clients', 'DESC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function get_duplicate_clients()
    {
        $this->db->select("client_id, client_name, COUNT(client_name), client_address, COUNT(client_address)");
        $this->db->where("client_name != ''");
        $this->db->group_by('client_name, client_address');
        $this->db->having('(COUNT(client_name) > 1) AND (COUNT(client_address) > 1)');
        $result = $this->db->get('clients');
        return $result;
        //SELECT client_id, client_name, COUNT(client_name), client_address, COUNT(client_address) FROM clients
        //WHERE client_name != ''
        //GROUP BY client_name, client_address HAVING (COUNT(client_name) > 1) AND (COUNT(client_address) > 1)

    }

    function get_insert_notes($where = '', $like = [], $join = NULL)
    {

        if ($join) {
            $this->db->select("client_notes.*, estimates.lead_id as est_lead_id");
            $this->db->join('estimates', 'client_notes.client_id = estimates.client_id', 'left');
        }
        if ($where != '')
            $this->db->where($where);
        if (!empty($like))
            $this->db->like($like);
        $result = $this->db->get('client_notes');
        return $result;
        //SELECT client_notes.*, UNIX_TIMESTAMP(client_notes.client_note_date) + 25200 as DATE_TIME, estimates.estimate_id, estimates.date_created
        //FROM `client_notes`
        //LEFT JOIN estimates ON client_notes.client_id = estimates.client_id
        //WHERE estimates.date_created >= (UNIX_TIMESTAMP(client_notes.client_note_date) + 25198)
        //AND estimates.date_created <= (UNIX_TIMESTAMP(client_notes.client_note_date) + 25202)
        //AND client_note LIKE "%Insert services:%"
    }


    public function get_papers($wdata = array(), $order_by = '')
    {

        $this->db->select("client_papers.*, users.id, CONCAT(users.firstname, ' ',  users.lastname) AS cp_author, users.emailid", FALSE);
        $this->db->join('users', 'client_papers.cp_user_id = users.id');
        if (!empty($wdata))
            $this->db->where($wdata);

        if ($order_by == '')
            $order_by = 'cp_id ASC';
        $this->db->order_by($order_by);

        $query = $this->db->get($this->table1);

        return $query->result_array();

    }// end get_notes;


//*******************************************************************************************************************
//*************
//*************            								post_notes($id);
//*************
//*******************************************************************************************************************
//Insertin a new note for the customer's profile.
    public function add_client_papers($data)
    {
        //Inseting values;
        if ($this->db->insert($this->table1, $data)) {
            return $this->db->insert_id();

        } else {
            return FALSE;

        }
    }//end of post_notes;

//*******************************************************************************************************************
//*************
//*************            								delete_notes($id);
//*************
//*******************************************************************************************************************

    function delete_client_papers($id)
    {
        if ($id) {
            $this->db->where('cp_id', $id);
            $this->db->delete($this->table1);
            return TRUE;
        }
        return FALSE;
    }

    function delete_all_client_papers($client_id)
    {
        if ($client_id) {
            $this->db->where('cp_client_id', $client_id);
            $this->db->delete($this->table1);
            return TRUE;
        }
        return FALSE;
    }


    function get_client_with_last_lead($wdata = [])
    {
        $this->db->select("clients.*, leads.*, clients.client_id as client_id, CONCAT(users.firstname, ' ', users.lastname) as estimator, cli.client_name as refferal", FALSE);
        $this->db->from('clients');
        $this->db->join('leads', 'clients.client_id = leads.client_id', 'left');
        $this->db->join('users', 'leads.lead_reffered_user = users.id', 'left');
        $this->db->join('clients cli', 'leads.lead_reffered_client = cli.client_id', 'left');
        if (!empty($wdata))
            $this->db->where($wdata);
        $this->db->group_by('clients.client_id');
        //lead_reffered_by
        $query = $this->db->get();
        return $query->result();
    }

    public function get_client_by_contacts($wdata = array(), $limit = '', $start = '')
    {
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1 AND cc_email_check = 1');
        if (!empty($wdata) && $wdata)
            $this->db->where($wdata);
        if ($limit != '') {
            $this->db->limit($limit, $start);
        }
        $this->db->group_by('clients_contacts.cc_email');

        return $this->db->get($this->table);
    }

    function global_search_clients($where = [], $date = [], $services = [], $estimate_price = [], $service_price = [], $estimator = '', $status = [], $note = '', $invoice_price = [], $limit = '', $start = '')
    {
        //$this->db->_protect_identifiers = false;
        $wdata_client = $wdata_tasks = $wdata_leads = $wdata_wo = $wdata_in = '';
        $this->db->select("clients.*,  estimates.estimate_id, estimate_statuses.est_status_name, CONCAT(estimator.firstname, ' ', estimator.lastname) AS estimator, clients.client_name, clients_contacts.cc_phone, clients_contacts.cc_email, (SUM(estimates_services.service_price) /*/ IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1)*/) as estimate_price", FALSE);


        $this->db->join('leads', 'clients.client_id = leads.client_id', $where['search_by']['leads']);
        $this->db->join('estimates', 'estimates.lead_id = leads.lead_id ', $where['search_by']['estimates']);
        $this->db->join('estimates_services', 'estimates.estimate_id = estimates_services.estimate_id', 'left');
        $this->db->join('services', 'estimates_services.service_id = services.service_id', 'left');
        $this->db->join('estimate_statuses', 'estimates.status = estimate_statuses.est_status_id', 'left');
        $this->db->join('users estimator', 'estimates.user_id = estimator.id', 'left');


        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1 AND cc_email_check = 1');
        //$this->db->join('client_tasks', 'clients.client_id = client_tasks.task_client_id', 'left');

        $this->db->join('workorders', 'estimates.estimate_id = workorders.estimate_id', $where['search_by']['workorders']);
        $this->db->join('invoices', 'estimates.estimate_id = invoices.estimate_id', $where['search_by']['invoices']);
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');


        if (isset($where['clients']) && is_array($where['clients']) && !empty($where['clients'])) {
            if (isset($where['clients']['clients.client_date_created >'])) {
                $wdata_client .= "clients.client_date_created >= '" . $where['clients']['clients.client_date_created >'] . "' AND ";
                unset($where['clients']['clients.client_date_created >']);
            }
            if (isset($where['clients']['clients.client_date_created <'])) {
                $wdata_client .= "clients.client_date_created <= '" . $where['clients']['clients.client_date_created <'] . "' AND ";
                unset($where['clients']['clients.client_date_created <']);
            }
            foreach ($where['clients'] as $k => $v)
                $wdata_client .= $k . " = '" . $v . "' AND ";
            $wdata_client = rtrim($wdata_client, ' AND ');
            $this->db->where($wdata_client);
        }

        //LEADS
        if (isset($where['leads']) && is_array($where['leads']) && !empty($where['leads'])) {
            if (isset($where['leads']['leads.lead_date_created >'])) {
                $wdata_leads .= "leads.lead_date_created >= '" . $where['leads']['leads.lead_date_created >'] . "' AND ";
                unset($where['leads']['leads.lead_date_created >']);
            }
            if (isset($where['leads']['leads.lead_date_created <'])) {
                $wdata_leads .= "leads.lead_date_created <= '" . $where['leads']['leads.lead_date_created <'] . "' AND ";
                unset($where['leads']['leads.lead_date_created <']);
            }
            foreach ($where['leads'] as $k => $v)
                $wdata_leads .= $k . ' = "' . $v . '" AND ';
            $wdata_leads = rtrim($wdata_leads, ' AND ');

            $this->db->where($wdata_leads);
        }
        //END LEADS

        //ESTIMATES
        if (is_array($date) && !empty($date)) {
            if (count($date) > 1)//countOk
                $queryDate = '(estimates.date_created >= ' . $date['date_created >='] . ' AND estimates.date_created <= ' . $date['date_created <='] . ')';
            else {
                foreach ($date as $k => $v)
                    $queryDate = '(estimates.' . $k . ' ' . $v . ')';
            }
            $this->db->where($queryDate);
        }
        if (is_array($services) && !empty($services)) {
            $queryServices = '(';

            foreach ($services as $k => $v) {
                $queryServices .= 'estimates_services.service_id = ' . $v;
                if (isset($services[$k + 1]))
                    $queryServices .= ' OR ';
            }
            $queryServices .= ')';
            $this->db->where($queryServices);
        }
        if (is_array($service_price) && !empty($service_price)) {
            if (count($service_price) > 1)//countOk
                $querySerPrice = '(estimates_services.service_price >= "' . $service_price['service_price >='] . '" AND estimates_services.service_price <= "' . $service_price['service_price <='] . '")';
            else {
                foreach ($service_price as $k => $v)
                    $querySerPrice = '(' . $k . ' "' . $v . '")';
            }
            $this->db->where($querySerPrice);
        }
        if ($estimator != '')
            $this->db->where('estimates.user_id', $estimator);

        if (!empty($status)) {
            $queryServices = '(';

            foreach ($status as $k => $v) {
                $queryServices .= 'estimates.status = ' . $v;
                if (isset($status[$k + 1]))
                    $queryServices .= ' OR ';
            }
            $queryServices .= ')';
            $this->db->where($queryServices);
        }


        if ($note != '') {
            $this->db->like('estimates.estimate_item_team', $note);
            $this->db->or_like('estimates.estimate_item_estimated_time', $note);
            $this->db->or_like('estimates.estimate_item_note_crew', $note);
            $this->db->or_like('estimates.estimate_item_note_estimate', $note);
            $this->db->or_like('estimates.estimate_item_note_payment', $note);
            $this->db->or_like('estimates_services.service_description', $note);
            $this->db->or_like('services.service_description', $note);
        }
        //END ESTIMATES
        //WO
        if (isset($where['workorders']) && !empty($where['workorders'])) {
            if (isset($where['workorders']['workorders.date_created >'])) {
                $wdata_wo .= "workorders.date_created >= '" . $where['workorders']['workorders.date_created >'] . "' AND ";
                unset($where['workorders']['workorders.date_created >']);
            }
            if (isset($where['workorders']['workorders.date_created <'])) {
                $wdata_wo .= "workorders.date_created <= '" . $where['workorders']['workorders.date_created <'] . "' AND ";
                unset($where['workorders']['workorders.date_created <']);
            }
            if (isset($where['workorders']['workorders.wo_status']) && !empty($where['workorders']['workorders.wo_status'])) {
                $queryStatuses = '(';
                foreach ($where['workorders']['workorders.wo_status'] as $k => $v) {
                    $queryStatuses .= 'workorders.wo_status = ' . $v;
                    if (isset($where['workorders']['workorders.wo_status'][$k + 1]))
                        $queryStatuses .= ' OR ';
                }
                $queryStatuses .= ')';
                unset($where['workorders']['workorders.wo_status']);

                $this->db->where($queryStatuses);
            }
            if ($wdata_wo != '') {
                $wdata_wo = rtrim($wdata_wo, ' AND ');
                $this->db->where($wdata_wo);
            }
        }

        //END WO
        //INVOICES
        if (isset($where['invoices']) && !empty($where['invoices'])) {
            if (isset($where['invoices']['invoices.date_created >'])) {
                $wdata_in .= "invoices.date_created >= '" . $where['invoices']['invoices.date_created >'] . "' AND ";
                unset($where['invoices']['invoices.date_created >']);
            }
            if (isset($where['invoices']['invoices.date_created <'])) {
                $wdata_in .= "invoices.date_created <= '" . $where['invoices']['invoices.date_created <'] . "' AND ";
                unset($where['invoices']['invoices.date_created <']);
            }
            if (isset($where['invoices']['invoices.in_status']) && !empty($where['invoices']['invoices.in_status'])) {
                $queryStatuses = '(';
                foreach ($where['invoices']['invoices.in_status'] as $k => $v) {
                    $queryStatuses .= 'invoices.in_status = ' . $v;
                    if (isset($where['invoices']['invoices.in_status'][$k + 1]))
                        $queryStatuses .= ' OR ';
                }
                $queryStatuses .= ')';
                unset($where['invoices']['invoices.in_status']);

                $this->db->where($queryStatuses);
            }
            if ($wdata_in != '') {
                $wdata_in = rtrim($wdata_in, ' AND ');
                $this->db->where($wdata_in);
            }
        }

        //END INVOICES
        $this->db->where('(clients.client_unsubscribe IS NULL OR clients.client_unsubscribe = 0)');

        $this->db->group_by('clients_contacts.cc_email');
        $this->db->order_by('clients.client_id', 'ASC');

        //INVOICES PRICE
        if (is_array($estimate_price) && !empty($estimate_price)) {
            if (count($estimate_price) > 1)//countOk
                $this->db->having('(SUM(estimates_services.service_price) /* / IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1) */ >= "' . $estimate_price['>='] . '" AND SUM(estimates_services.service_price) /* / IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1) */ <= "' . $estimate_price['<='] . '")');
            else {
                foreach ($estimate_price as $k => $v)
                    $this->db->having('(SUM(estimates_services.service_price) /* / IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1) */ ' . $k . ' "' . $v . '")');
            }
        }
        //END INVOICES PRICE
        //ESTIMATES PRICE
        if (is_array($invoice_price) && !empty($invoice_price)) {
            if (count($invoice_price) > 1)//countOk
                $this->db->having('(SUM(estimates_services.service_price) /* / IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1) */ >= "' . $invoice_price['>='] . '" AND SUM(estimates_services.service_price) /* / IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1) */ <= "' . $invoice_price['<='] . '")');
            else {
                foreach ($invoice_price as $k => $v)
                    $this->db->having('(SUM(estimates_services.service_price) /* / IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1) */ ' . $k . ' "' . $v . '")');
            }
        }
        //END ESTIMATES PRICE
        if ($limit != '') {
            $this->db->limit($limit, $start);
        }

        $result = $this->db->get('clients');
        return $result;
    }

    function get_nl($where = '', $limit = 0, $offset = 0, $count = FALSE, $group_by = '')
    {
        $this->db->join('clients', 'clients.client_id = newsletters.nl_client');
        $this->db->join('users', 'users.id = newsletters.nl_estimator', 'left');
        if ($where != '')
            $this->db->where($where);
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        if ($group_by != '') {
            $this->db->group_by($group_by);
        }
        $this->db->order_by('nl_date');
        $result = $this->db->get('newsletters');
        if ($count)
            return $result->num_rows();
        return $result->result();
    }

    function insert_nl($data)
    {
        if ($this->db->insert('newsletters', $data))
            return TRUE;
        return FALSE;
    }

    function insert_batch_nl($data)
    {
        if ($this->db->insert_batch('newsletters', $data))
            return TRUE;
        return FALSE;
    }

    public function update_nl($id, $data)
    {
        $this->db->where('nl_id', $id);
        if ($this->db->update('newsletters', $data))
            return TRUE;
        return FALSE;
    }

    function delete_letter($id)
    {
        $this->db->where('nl_id', $id);
        $this->db->delete('newsletters');
        return TRUE;
    }

    public function get_payment_by_qb_id($qb_id)
    {
        $this->db->select('client_payments.*, estimates.*, clients.*, invoices.invoice_qb_id');
        $this->db->where('client_payments.payment_amount <>', 0);
        $this->db->where('client_payments.payment_qb_id = ', $qb_id);
        $this->db->join('estimates', 'estimates.estimate_id = client_payments.estimate_id');
        $this->db->join('clients', 'clients.client_id = estimates.client_id', 'left');
        $this->db->join('invoices', 'invoices.estimate_id = estimates.estimate_id', 'left');
        return $this->db->get('client_payments')->row();
    }
    
    function get_clients_app($filters, $limit, $offset, $order) {
        $this->db->select("cl.client_type, cl.client_id, cl.client_brand_id, cl.client_name, cl.client_lat, "
                          ."cl.client_lng, cl.client_address, cl.client_city, cc.cc_name, cc.cc_phone, cc.cc_email, "
                          ."cl.client_zip, cl.client_state, cl.client_country, GROUP_CONCAT(DISTINCT(tags.name)) as client_tags"); //, SUM(sv.service_price) as estimate_price
        $this->db->from('clients cl');
        $this->db->join('clients_contacts cc', 'cl.client_id = cc.cc_client_id AND cc.cc_print = 1', 'left');
        $this->db->join('leads ld', 'cl.client_id = ld.client_id', 'left');
        $this->db->join('estimates es', 'ld.lead_id = es.lead_id', 'left');
        $this->db->join('estimates_services sv', 'es.estimate_id = sv.estimate_id AND service_status <> 1', 'left');
        $this->db->join('client_tags', 'client_tags.client_id = cl.client_id', 'left');
        $this->db->join('tags', 'tags.tag_id = client_tags.tag_id', 'left');

        $this->db->where('cc.cc_print', 1);        
        if(isset($filters['date_from']) && $filters['date_from'] != null && $filters['date_from'] != ''){            
            $this->db->where('es.date_created >=', strtotime($filters['date_from']));            
        }
        if(isset($filters['date_to']) && $filters['date_to'] != null && $filters['date_to'] != ''){
            $this->db->where('es.date_created <=', strtotime($filters['date_to'] . ' 23:59:59'));
        }
        if(isset($filters['is_confirmed']) && $filters['is_confirmed'] != null && $filters['is_confirmed'] != ''){
            $this->db->where('es.status', 6);            
        }
        if(isset($filters['total_from']) && $filters['total_from'] != null && $filters['total_from'] != ''){
            $this->db->having('SUM(sv.service_price) >=', $filters['total_from']);
        }
        if(isset($filters['total_to']) && $filters['total_to'] != null && $filters['total_to'] != ''){
            $this->db->having('SUM(sv.service_price) <=', $filters['total_to']);
        }
        if(isset($filters['estimator_id']) && $filters['estimator_id'] != null && $filters['estimator_id'] != ''){
            $this->db->where('es.user_id', $filters['estimator_id']);
        }
        if(isset($filters['client_type']) && $filters['client_type'] != null && $filters['client_type'] != ''){
            $this->db->where('cl.client_type', $filters['client_type']);
        }
        if(isset($filters['client_maker']) && $filters['client_maker'] != null && $filters['client_maker'] != ''){
            $this->db->where('cl.client_maker', $filters['client_maker']);
        }
        if (isset($filters['client_brand_id'])){
            $this->db->where('cl.client_brand_id', $filters['client_brand_id']);
        }

        if(isset($filters['tag_names']) && !empty($filters['tag_names'])) {
            $this->db->where_in('tags.name', $filters['tag_names']);
        }

        if(isset($filters['search']) && $filters['search'] != null && $filters['search'] != ''){
            $this->db->where("(cl.client_name like '%".$filters['search']."%' OR cl.client_address like '%".$filters['search']."%' OR "
                ."cl.client_city like '%".$filters['search']."%' OR cc.cc_email like '%".$filters['search']."%' OR "
                ."cc.cc_phone like '%".$filters['search']."%' OR cc.cc_name like '%".$filters['search']."%')", NULL, FALSE);
        }
        
        if ($limit != '') {
            $this->db->limit($limit, $offset);
        }
        
        $this->db->group_by('cl.client_id');

        $CI =& get_instance();

        if ($order) {
            $this->db->order_by($order);
        }
        $query = $this->db->get();

        if($query->num_rows() > 0){
            return $query->result();
        } else {
            return [];
        }
    }
    
    function record_count_app($filters) {
        $this->db->select('DISTINCT(cl.client_id)');
        $this->db->from('clients cl');
        $this->db->join('clients_contacts cc', 'cl.client_id = cc.cc_client_id', 'left');
        $this->db->join('leads ld', 'cl.client_id = ld.client_id', 'left');
        $this->db->join('estimates es', 'ld.lead_id = es.lead_id', 'left');
        $this->db->join('estimates_services sv', 'es.estimate_id = sv.estimate_id', 'left');
        $this->db->join('client_tags', 'client_tags.client_id = cl.client_id', 'left');
        $this->db->join('tags', 'tags.tag_id = client_tags.tag_id', 'left');

        $this->db->where('cc.cc_print', 1);
        if(isset($filters['date_from']) && $filters['date_from'] != null && $filters['date_from'] != ''){            
            $this->db->where('es.date_created >=', strtotime($filters['date_from']));            
        }
        if(isset($filters['date_to']) && $filters['date_to'] != null && $filters['date_to'] != ''){            
            $this->db->where('es.date_created <=', strtotime($filters['date_to'] . ' 23:59:59'));
        }
        if(isset($filters['is_confirmed']) && $filters['is_confirmed'] != null && $filters['is_confirmed'] != ''){
            $this->db->where('es.status', 6);            
        }
        if(isset($filters['total_from']) && $filters['total_from'] != null && $filters['total_from'] != ''){
            $this->db->having('SUM(sv.service_price) >=', $filters['total_from']);
        }
        if(isset($filters['total_to']) && $filters['total_to'] != null && $filters['total_to'] != ''){
            $this->db->having('SUM(sv.service_price) <=', $filters['total_to']);
        }
        if(isset($filters['estimator_id']) && $filters['estimator_id'] != null && $filters['estimator_id'] != ''){
            $this->db->where('es.user_id', $filters['estimator_id']);
        }
        if(isset($filters['client_type']) && $filters['client_type'] != null && $filters['client_type'] != ''){
            $this->db->where('cl.client_type', $filters['client_type']);
        }
        if(isset($filters['client_maker']) && $filters['client_maker'] != null && $filters['client_maker'] != ''){
            $this->db->where('cl.client_maker', $filters['client_maker']);
        }
        if (isset($filters['client_brand_id'])){
            $this->db->where('cl.client_brand_id', $filters['client_brand_id']);
        }

        if(isset($filters['tag_names']) && !empty($filters['tag_names'])) {
            $this->db->where_in('tags.name', $filters['tag_names']);
        }

        if(isset($filters['search']) && $filters['search'] != null && $filters['search'] != ''){            
            $this->db->where("(cl.client_name like '%".$filters['search']."%' OR cl.client_address like '%".$filters['search']."%' OR "
                ."cl.client_city like '%".$filters['search']."%' OR cc.cc_email like '%".$filters['search']."%' OR "
                ."cc.cc_phone like '%".$filters['search']."%' OR cc.cc_name like '%".$filters['search']."%')", NULL, FALSE);
        }
        $this->db->group_by('cl.client_id');
        
        $query = $this->db->get();
        
        return $query->num_rows();  
    }

    function getPermissionsSubQuery() {

        if(!request()->user()) {
            return false;
        }

        $this->db->select('clients.client_id');
        $this->db->from('clients');
        $this->db->join('leads', 'leads.client_id = clients.client_id', 'left');
        $this->db->join('estimates', 'estimates.client_id = clients.client_id', 'left');
        $this->db->where('estimates.user_id', request()->user()->id);
        $this->db->or_where('leads.lead_author_id', request()->user()->id);
        $this->db->or_where('leads.lead_estimator', request()->user()->id);
        $this->db->group_by('clients.client_id');

        $permSubQuery = $this->db->_compile_select();
        $this->db->_reset_select();

        return $permSubQuery;
    }
    
}

//End model.
