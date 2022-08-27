<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$config = [
	'update_user'=>[
		['field'=>'txtfirstname', 'label'=>'First name', 'rules'=>'required'],
	   	['field'=>'txtlastname', 'label'=>'Last name', 'rules'=>'required'],
	   	['field'=>'selectusertype', 'label'=>'User Type', 'rules'=>'required'],
	   	
	   	
	],
	'create_user'=>[
		['field'=>'txtfirstname', 'label'=>'First name', 'rules'=>'required'],
	   	['field'=>'txtlastname', 'label'=>'Last name', 'rules'=>'required'],
	   	['field'=>'selectusertype', 'label'=>'User Type', 'rules'=>'required'],
	   	['field'=>'txtemail', 'label'=>'User Name', 'rules'=>'required'],
	   	['field'=>'txtpassword', 'label'=>'Password', 'rules'=>'required'], 	
	],

	'employee_to_user'=>[

		['field'=>'emp_email', 'label'=>'Email', 'rules'=>'valid_email'],
		['field'=>'txtemail', 'label'=>'User Name', 'rules'=>'trim|required|min_length[2]|max_length[20]'],
		//['field'=>'txtposition', 'label'=>'Position', 'rules'=>''],
		//['field'=>'txtaddress1', 'label'=>'Address line 1', 'rules'=>''],
		//['field'=>'empaddress2', 'label'=>'Address line 2', 'rules'=>''],
		//['field'=>'txtcity', 'label'=>'City', 'rules'=>''],
		//['field'=>'empstate', 'label'=>'State', 'rules'=>''],
		//['field'=>'txtphone', 'label'=>'Phone', 'rules'=>''],
		//['field'=>'txtsin', 'label'=>'Sin', 'rules'=>''],
		//['field'=>'txttype', 'label'=>'Type', 'rules'=>''],
		//['field'=>'is_field_estimator', 'label'=>'Estimator', 'rules'=>''],
		//['field'=>'is_feild_worker', 'label'=>'Worker', 'rules'=>''],
		//['field'=>'driver', 'label'=>'Driver', 'rules'=>''],
		//['field'=>'climber', 'label'=>'Climber', 'rules'=>''],
		//['field'=>'area_account_message', 'label'=>'Msg', 'rules'=>''],
		['field'=>'deductions_amount', 'label'=>'Deductions Amount', 'rules'=>'numeric'],
		['field'=>'deductions_state', 'label'=>'Deductions State', 'rules'=>'is_natural'],
		//['field'=>'deductions_desc', 'label'=>'Deductions Description', 'rules'=>''],
		//['field'=>'txtstarttime', 'label'=>'Employee Start Time', 'rules'=>''],
		//['field'=>'txthiredate', 'label'=>'Employee Date Of Hire', 'rules'=>''],
		//['field'=>'txtbirthday', 'label'=>'Employee Date Of Birthday', 'rules'=>''],
		//['field'=>'txthourlyrate', 'label'=>'Employee Hourly Rate', 'rules'=>'callback_admin_required'],
		//['field'=>'txtyearlyrate', 'label'=>'Employee Yearly Rate', 'rules'=>'callback_admin_required']
	],
	
	'equipment_service_settings'=>[
		['field'=>'service_type', 'label'=>'Service Type', 'rules'=>'required|numeric'],
		['field'=>'service_months', 'label'=>'Month Periodicity', 'rules'=>'required|numeric'],
		['field'=>'item_id', 'label'=>'Equipment', 'rules'=>'required'],
		['field'=>'service_start', 'label'=>'Service Date Start', 'rules'=>'required'],
		['field'=>'report_kilometers', 'label'=>'Counter Value', 'rules'=>'numeric']
	],
	
	'equipment_service_settings_update'=>[
		['field'=>'service_type', 'label'=>'Service Type', 'rules'=>'required|numeric'],
		['field'=>'service_months', 'label'=>'Month Periodicity', 'rules'=>'required|numeric'],
		['field'=>'item_id', 'label'=>'Equipment', 'rules'=>'required'],
	],

	'followup_settings'=>[
		['field'=>'fs_table', 'label'=>'Module', 'rules'=>'required'],
		['field'=>'fs_type', 'label'=>'Type', 'rules'=>'required'],
		['field'=>'fs_template', 'label'=>'Template', 'rules'=>''],
		['field'=>'fs_periodicity', 'label'=>'Periodicity', 'rules'=>'required|is_natural_no_zero'],
	],

	'products'=>[
		['field'=>'service_name', 'label'=>'name', 'rules'=>'required|is_unique[services.service_name]', 'errors' => ['is_unique' => 'This name already exists.']],
		['field'=>'service_description', 'label'=>'description', 'rules'=>'required'],
		['field'=>'cost', 'label'=>'cost', 'rules'=>'required|numeric'],
	],

    'update_product'=>[
        ['field'=>'service_name', 'label'=>'name', 'rules'=>'required|callback_is_unique_name'],
        ['field'=>'service_description', 'label'=>'description', 'rules'=>'required'],
        ['field'=>'cost', 'label'=>'cost', 'rules'=>'required|numeric'],
    ],

    'bundles'=>[
        ['field'=>'bundle_name', 'label'=>'name', 'rules'=>'required|is_unique[services.service_name]', 'errors' => ['is_unique' => 'This name already exists.']],
        ['field'=>'bundle_description', 'label'=>'description', 'rules'=>'required'],
        ['field'=>'bundle_services', 'label'=>'items', 'rules'=>'required|callback_has_bundle_services'],
    ],

    'update_bundle'=>[
        ['field'=>'bundle_name', 'label'=>'bundle name', 'rules'=>'required|callback_is_unique_name'],
        ['field'=>'bundle_description', 'label'=>'description', 'rules'=>'required'],
        ['field'=>'bundle_services', 'label'=>'items', 'rules'=>'required'],
    ],

    'categories' => [
        ['field'=>'categoryName', 'label'=>'Name', 'rules'=>'required']
    ],

    'classes' => [
        ['field'=>'className', 'label'=>'Name', 'rules'=>'required']
    ]
];
