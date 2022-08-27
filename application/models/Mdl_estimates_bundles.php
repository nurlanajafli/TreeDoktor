<?php
class Mdl_estimates_bundles extends JR_Model
{
    protected $_table = 'estimates_bundles';
    protected $primary_key = 'id';

    function get_estimates_bundles_records ($where = []){
        $this->db->select('estimates_services.service_id, estimates_services.quantity, estimates_services.estimate_service_class_id,
                            estimates_services.cost, estimates_services.service_description,
                            estimates_services.non_taxable, services.service_name, estimates_services.service_price, estimates_services.id, services.is_product, services.service_status');
        if (count($where))
            $this->db->where($where);
        $this->db->join('estimates_services', 'estimates_services.id = estimates_bundles.eb_service_id');
        $this->db->join('services', 'services.service_id = estimates_services.service_id');
        $result = $this->db->get($this->_table)->result();
        return $result;
    }


}
