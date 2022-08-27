<?php 
if (!defined('BASEPATH'))
	exit('No direct script access allowed');



function inventory_path($client_id, $file=FALSE){
	$path = 'uploads/tree_inventory/';
	$path .= $client_id . '/';
	if($file)
		$path .= $file;

	return $path;
}

function inventory_screen_path($client_id, $file=FALSE){
	$path = 'uploads/tree_inventory/screen/';
	$path .= $client_id . '/';
	if($file)
		$path .= $file;

	return $path;
}

function inventory_pic($inventory, $nopic=false)
{
	if(!empty($inventory)){
		
		$inventory = sort_inventory((array)$inventory);

		$inventory = array_map(function($tree) use ($nopic) {
			if($tree->ti_file){
                // get file from s3
			    if(stripos($tree->ti_file, 'uploads') !== false)
                    $tree->ti_file = base_url($tree->ti_file);
			    else
				    $tree->ti_file = base_url(inventory_path($tree->ti_tis_id, $tree->ti_file));
			}else{
				$tree->ti_file = $nopic;
			} 
			return $tree; 
		} , $inventory);
	}
	
	return $inventory;
}

function sort_inventory($inventory)
{	
	$ti_tree_numbers = array_map(function($e) {
	    return is_object($e) ? $e->ti_tree_number : $e['ti_tree_number'];
	}, $inventory);
	
	$inventory_array = array_map(function($e) {
	    return (array)$e;
	}, $inventory);

	if(count($inventory_array) != count($ti_tree_numbers)){//countOk
		return $inventory;
	}
	//SORT_NATURAL - compare items as strings using "natural ordering" like natsort()
	//SORT_FLAG_CASE
	array_multisort($ti_tree_numbers, SORT_ASC, SORT_NATURAL, $inventory_array);
	$inventory = array_map(function($e) {
	    return (object)$e;
	}, $inventory_array);

	return $inventory;
}

function sort_condition($a, $b)
{
	if($a>$b)
		return true;

	return false; 
}

function work_types_string($types, $work_types, $short=true)
{
				
	if(!is_array($types) || empty($types))
		return '';

	foreach ($work_types as $key => $value) {
		$work_types_assoc[$value->ip_id] = $value;
	}
	
	$result_array = [];
	foreach($types as $key=>$value)
	{
		if(isset($work_types_assoc[$value->tiwt_work_type_id]))
		{
			$name = $work_types_assoc[$value->tiwt_work_type_id]->ip_name_short;
			if($short==false)
				$name=$name.':'.$work_types_assoc[$value->tiwt_work_type_id]->ip_name;

			$result_array[] = $name;
		}
	}
	
	return implode(', ', $result_array);
}

function inventory_map_image_path($client_id, $lead_id, $file = FALSE)
{
	$path = 'uploads/tree_inventory/';

	$path .= $client_id . '/'. $lead_id . '/';

	if($file)
		$path .= $file;

	return $path;
}

function inventory_map_image($client_id, $lead_id, $file = FALSE){
	$path = inventory_map_image_path($client_id, $lead_id, $file);
	return base_url($path); 
}

function tree_inventory_project_overlay_path($client_id, $tis_id, $file_name = FALSE){
    $path = 'uploads/clients_files/';
    $path .= $client_id . '/projects/' . $tis_id . '/';
    if($file_name)
        $path .= $file_name;

    return $path;
}