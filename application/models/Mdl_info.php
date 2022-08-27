<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

//*******************************************************************************************************************
//*************
//*************																							mdl_info:
//*******************************************************************************************************************	

class Mdl_info extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'trees_info';
		$this->primary_key = 'trees_info.tree_id';
	}
}
//end of file mdl_reports.php
