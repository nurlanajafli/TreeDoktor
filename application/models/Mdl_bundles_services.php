<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: september - 2014
 */

class Mdl_bundles_services extends JR_Model
{
    protected $_table = 'bundles_services';
    protected $primary_key = 'bundle_service_id';

    function update_priority($updateBatch)
    {
        $this->db->update_batch($this->_table, $updateBatch, 'service_id');
        return TRUE;
    }

    function find_all($wdata = array(), $order = '')
    {
        if (!empty($wdata)) {
            $this->db->where($wdata);
        }
        if ($order) {
            $this->db->order_by($order);
        }
        return $this->db->get($this->_table)->result();
    }

    function get_all_bundle_services($search_array = array(), $limit = '', $start = '', $order = '', $wdata = array())
    {
        if (count($wdata))
            $this->db->where($wdata);
        if ($limit != '') {
            $this->db->limit($limit, $start);
        }

        if (isset($search_array)) {
            $this->db->or_like($search_array);
        }

        if ($order) {
            $this->db->order_by($order);
        }
        //$this->db->join('sub_services', 'services.service_id=sub_services.sub_service_id', 'left');
        return $this->db->get($this->_table)->result();
    }

    function insert_estimates_bundles_records($data){
        if ($data !== FALSE) {
            $id = $this->_database->insert('estimates_bundles_records', $data);
            return $id;
        } else {
            return FALSE;
        }
    }

    function get_estimates_bundles_records($wdata = []){
        $this->db->select('estimates_bundles_records.bundle_id, estimates_bundles_records.record_id, estimates_bundles_records.estimate_bundle_record_qty,
                            estimates_bundles_records.estimate_bundle_record_cost, estimates_bundles_records.estimate_bundle_record_description,
                            estimates_bundles_records.non_taxable, services.service_name');
        if (count($wdata))
            $this->db->where($wdata);
        $this->db->join('services', 'services.service_id = estimates_bundles_records.record_id');
        $result = $this->db->get('estimates_bundles_records')->result();

        return $result;
    }
    function update_estimates_bundles_records($data, $wdata){
        if ($data !== FALSE) {
            $this->_database->where($wdata);
            $this->_database->update('estimates_bundles_records', $data);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function delete_estimates_bundles_records($wdata){
        $where = func_get_args();
        $where = $this->trigger('before_delete', $where);
        $this->_set_where($where);
        if ($this->soft_delete) {
            $result = $this->_database->update('estimates_bundles_records', array($this->soft_delete_key => TRUE));
        } else {
            $result = $this->_database->delete('estimates_bundles_records');
        }
        $this->trigger('after_delete', $result);
        return $result;
    }

}
