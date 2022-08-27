<?php
class Mdl_settings_orm extends JR_Model
{
	protected $_table = 'settings';
	protected $primary_key = 'stt_id';

	//public $belongs_to = array('mdl_worked' => array('primary_key' => 'login_worked_id', 'model' => 'reports/mdl_worked'));
	
	/*
	stt_key_name
	stt_key_value
	stt_key_validate
	stt_section
    stt_label
	*/
	public function __construct() {
		parent::__construct();
	}

	public function install()
	{
		$all = $this->get_all();
		if(empty($all))
			return true;

		foreach ($all as $key => $item) {
			$this->config->set_item($item->stt_key_name, $item->stt_key_value);
		}

		$this->load->config('brands');
		
		if ($this->db->table_exists('brands'))
		{
	        $brands = $this->db->where('brands.deleted_at is NULL')
			->select('brands.*, COUNT(brand_images.bi_id) as count_images, brand_contacts.*')
			->from('brands')
			->join('brand_images', 'brand_images.bi_brand_id=brands.b_id AND brand_images.deleted_at IS NULL', 'left')
			->join('brand_contacts', 'brand_contacts.bc_brand_id=brands.b_id AND brand_contacts.deleted_at IS NULL', 'left')
			->group_by('brands.b_id')
			->get()->result();

			$result = [];
			if($brands){
				foreach ($brands as $bkey => $brand) {
					foreach (config_item("logos") as $key => $value) {
						$brand->images[$value['logo_file']] = $value;	
					}
					
					$images = [];
					if($brand->count_images){
						$images = $this->db->where('bi_brand_id', $brand->b_id)->where('deleted_at is NULL')->from('brand_images')->get()->result();
					}

					foreach ($brand->images as $key => $value) {
						$brand->images[$key]['url'] = $value['default_image'];
						foreach($images as $image){
							if($value['logo_file']==$image->bi_key){
								$brand->images[$key]['url'] = $image->bi_value ? '/uploads/brands/'. $brand->b_id .'/'. $image->bi_value : false;
								$brand->images[$key]['db'] = $image;
							}
						}
					}
					if($brand->b_is_default){
						$this->config->set_item('company_header_logo_string', $brand->b_name);
						$this->config->set_item('company_name_short', $brand->b_name);
						$this->config->set_item('company_name_long', $brand->b_name);
						$this->config->set_item('default_email_from', $brand->b_name);
						$this->config->set_item('default_email_from_second', $brand->b_name);
						$this->config->set_item('account_email_address', $brand->bc_email);
						$this->config->set_item('office_address', $brand->b_company_address);
						$this->config->set_item('office_region', $brand->b_company_region);
						$this->config->set_item('office_city', $brand->b_company_city);
						$this->config->set_item('office_state', $brand->b_company_state);
						$this->config->set_item('office_zip', $brand->b_company_zip);
						$this->config->set_item('office_country', $brand->b_company_country);

						$this->config->set_item('office_phone', config_item('phone_country_code') . $brand->bc_phone);
						$this->config->set_item('office_phone_mask', numberTo($brand->bc_phone));
						$this->config->set_item('company_site', $brand->bc_site);
						$this->config->set_item('company_site_name_upper', $brand->bc_site);
						$this->config->set_item('company_site_http', str_replace('https://', 'http://', $brand->bc_site));
						$this->config->set_item('company_site_name', str_replace(['https://', 'http://'], '', $brand->bc_site));

						$this->config->set_item('map_lat', $brand->b_company_lat ?: 0);
						$this->config->set_item('map_lon', $brand->b_company_lng ?: 0);
						$this->config->set_item('map_center', config_item('map_lat') . ', ' . config_item('map_lon'));
						$this->config->set_item('office_lat', $brand->b_company_lat ?: 0);
						$this->config->set_item('office_lon', $brand->b_company_lng ?: 0);
						$this->config->set_item('office_location', config_item('office_lat') . ', ' . config_item('office_lon'));

						$this->config->set_item('office_address_map',
                            str_replace(' ', '+', config_item('office_address')) . '+' .
                            config_item('office_city') . '+' .
                            config_item('office_state') . '+' .
                            str_replace(' ', '+', config_item('office_zip'))
                        );

						foreach ($brand->images as $key => $value) {
							$config_key = isset($value['config_key'])?$value['config_key']:$value['logo_filename'];
							$config_value = $value['url'];
							$this->config->set_item($config_key, $config_value);
						}
					}

					$result[$brand->b_id] = $brand;
				}
				$this->config->set_item('brands', $result);
			}
		}


		$this->load->helper('settings');
		additional_tax_settings();
		autocomplete_restriction();
		debugbar_by_cookie();
	}

	public function settings_by_sections()
	{
		$all = $this->get_all();
		if(empty($all))
			return [];

		$result = [];
		foreach ($all as $key => $item) {
			if($item->stt_section && !$item->stt_is_hidden)
				$result[$item->stt_section][$item->stt_key_name] = $item;
		}

		return $result;
	}

}
