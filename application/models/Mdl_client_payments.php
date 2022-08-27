<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Mdl_client_payments extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->table = 'client_payments';
        $this->primary_key = "payment_id";
    }

    //*******************************************************************************************************************
//*************
//*************			Insert Invoice Function; Returns insert id or false; 
//*************
//*******************************************************************************************************************	

    function insert($data)
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
//*************			Get Payment details by ID
//*************
//*******************************************************************************************************************	
    function fetch($id)
    {

        if ($id) {
            $this->db->where([$this->primary_key => $id]);
            $res = $this->db->get($this->table);
            $result = $res->result();
            return array_shift($result);
        } else {
            return false;
        }
    }

    function get($data)
    {

        if ($data) {
            $this->db->where($data);
            $res = $this->db->get($this->table);
            return $result = $res->result_array();
        } else {
            return [];
        }
    }


    //*******************************************************************************************************************
//*************
//*************			Delete Payment Record Function; Returns true or false; 
//*************
//*******************************************************************************************************************	

    function delete($id)
    {
        return $res = $this->db->delete($this->table, array($this->primary_key => $id));
    }

    //*******************************************************************************************************************
//*************
//*************			Update Payment Invoice Function; @params id
//*************
//*******************************************************************************************************************	

    function update_by_cond($cond, $data)
    {

        if ($data) {
            $this->db->where($cond);
            $insert = $this->db->update($this->table, $data);

            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                return FALSE;
            }
        } else {
            echo "data not received";
        }
    }

    function get_extra_fee_sum($estimate_id){
        $this->db->select('SUM(payment_fee) as fee');
        $this->db->from($this->table);
        $this->db->where('estimate_id', $estimate_id);
        return $this->db->get()->first_row();
    }
}

?>
