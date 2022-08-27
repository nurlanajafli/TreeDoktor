<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: september - 2014
 */

class Mdl_services extends JR_Model
{
	protected $_table = 'services';
	protected $primary_key = 'service_id';

	public $has_many = array('mdl_services' => array('primary_key' => 'service_parent_id', 'model' => 'mdl_services'));

	function __construct()
	{
		parent::__construct();
		//$this->table = 'services';
		//$this->primary_key = "services.service_id";
	}

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
	
	function get_all_services($search_array = array(), $limit = '', $start = '', $order = '', $wdata = array())
	{
		if (!empty($wdata))
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
	
	public function record_count($or_wdata = array(), $wdata = array())
	{

		$this->db->from($this->_table);

		if (!empty($or_wdata)) {
			$this->db->or_like($or_wdata);
		}

		if (!empty($wdata)) {
			$this->db->where($wdata);
		}

		return $num_results = $this->db->count_all_results();

		//print($this->db->last_query());

	}//end record_count

    public function get_all_bundles(){
        $this->db->join('services', 'bundles_services.service_id = services.service_id');
        $this->db->group_by('services.service_id');
        return $this->db->get('bundles_services')->result();
    }

    public function get_records_included_in_bundle($bundle_id){
        if (!empty($bundle_id)) {
            $this->db->where('bundle_id = ' . $bundle_id);
        }
        $this->db->join('services', 'services.service_id = bundles_services.service_id');
        $this->db->where('services.service_status = 1');
        return $this->db->get('bundles_services')->result();
    }


    /**
     * @deprecated
     * @param int $service_status
     * @return array
     */
    public function get_service_tags($service_status = 1)
    {
        $where = [
           'service_parent_id' => null,
           'service_status' => $service_status,
        ];

        $services = $this->order_by('service_priority')->get_many_by($where);

        if (empty($services)) {
            return [];
        }

        $serviceTags = [];
        $productTags = [];
        $bundleTags = [];

        foreach($services as $k => $v) {
            if ($v->is_product) {
                $productTags[ $k ][ 'key' ] = $v->service_id;
                $productTags[ $k ][ 'name' ] = $v->service_name;
            } elseif ($v->is_bundle) {
                $bundleTags[ $k ][ 'key' ] = $v->service_id;
                $bundleTags[ $k ][ 'name' ] = $v->service_name;
            } else {
                $serviceTags[ $k ][ 'key' ] = $v->service_id;
                $serviceTags[ $k ][ 'name' ] = $v->service_name;
            }
        }

        return compact('serviceTags', 'productTags', 'bundleTags');
    }
}
