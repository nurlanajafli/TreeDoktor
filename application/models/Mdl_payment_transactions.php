<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Not used
 * @deprecated
 */
// TODO: remove
class Mdl_payment_transactions extends MY_Model
{

    const STATUS_NOT_PROCESSED = 0;
    const STATUS_PRE_AUTH = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_DECLINED = 3;
    const STATUS_ERROR = 4;
    const STATUS_REVIEW = 5;

    function __construct()
    {
        parent::__construct();
        $this->table = 'payment_transactions';
        $this->primary_key = "payment_transaction_id";
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
        return $res = $this->db->delete($this->table, array("id" => $id));
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
}

?>
