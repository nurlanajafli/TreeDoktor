<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: september - 2014
 */

class Mdl_qa extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'qa';
		$this->primary_key = "qa.qa_id";
	}

}
