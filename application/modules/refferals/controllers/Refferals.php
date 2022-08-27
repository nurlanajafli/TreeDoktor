<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Refferals extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																						Info Controller;
//*************
//*******************************************************************************************************************	
	function __construct()
	{

		parent::__construct();

		//Checking if user is logged in;
		if (!isUserLoggedIn()) {
			redirect('login');
		}

		$this->_title = SITE_NAME;
		//load all common models and libraries here;
		$this->load->model('mdl_leads');
		$this->load->model('mdl_user');
	}

//*******************************************************************************************************************
//*************
//*************																									Index;
//*************
//*******************************************************************************************************************		 
	 
	
}
//end of file reports.php
