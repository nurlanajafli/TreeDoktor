<?php

function start_ws_process()
{
	$path = realpath('./') . '/';
	if(!isDaemonActive())
		exec('php ' . $path . 'index.php websocket start > /dev/null &');
	//if(!isNodeDaemonActive())
		//exec('nodejs ' . $path . 'ws.js > /dev/null &');
}

function isDaemonActive()
{
	$path = realpath('./') . '/';
	$status = getDaemonStatus();
	if($status['run'])
	{
		consolemsg("daemon already running info=" . $status['info']);
		return true;
	}
	consolemsg("there is no process with, last termination was abnormal...");
	consolemsg("try to unlink PID file...");
	consolemsg("ERROR");
	return false;
}

function isNodeDaemonActive()
{
	$path = realpath('./') . '/';
	$status = getNodeDaemonStatus();
	if($status['run'])
	{
		consolemsg("daemon already running info=" . $status['info']);
		return true;
	}
	consolemsg("there is no process with, last termination was abnormal...");
	consolemsg("try to unlink PID file...");
	consolemsg("ERROR");
	return false;
}

function getDaemonStatus()
{
	$result = array ('run'=>false);
	$output = null;
	exec("ps -aux", $output);
	foreach($output as $key => $val)
	{
		if(preg_match('/index.php websocket start$/is', $val))
		{
			$result['run'] = true;
			$result['info'] = $val;
			break;
		}
	}
	return $result;
}

function getNodeDaemonStatus()
{
	$result = array ('run'=>false);
	$output = null;
	exec("ps -aux", $output);
	foreach($output as $key => $val)
	{
		if(preg_match('/ws.js$/is', $val))
		{
			$result['run'] = true;
			$result['info'] = $val;
			break;
		}
	}
	return $result;
}

function consolestart()
{
	consolemsg("console - start");
}

function consolemsg($msg)
{
	$filename = 'uploads/echowslog.html';
	/*$file = null;
	if(!file_exists($filename))
	{
		$file = fopen($filename,"w+");
		fputs($file, "<!DOCTYPE html>\r\n<html>\r\n<head>\r\n<title>GC - console log</title>\r\n\r\n<meta charset=\"UTF-8\" />\r\n</head>\r\n<body>\r\n");
	}
	else
		$file = fopen($filename,"a+");

	//echo $msg."\r\n";
	fputs ($file, "[<b>".date("Y.m.d-H:i:s")."</b>]". $msg ."<br />\r\n");
	fclose($file);*/
}

function consoleend()
{
	consolemsg("console - end");
}

function is_dev_phone()
{
	$CI = & get_instance();
	$from = $CI->input->post('From');
	$to = $CI->input->post('To');
	$find = ['ruslanGleba', 'dmitriyVashchenko'];
	
	foreach($find as $k=>$v)
	{
		if(strpos($from, $v) !== FALSE || strpos($to, $v) !== FALSE)
			return TRUE;
	}
	
	if($CI->input->post('TaskAttributes'))
	{
		$json = json_decode($CI->input->post('TaskAttributes'));
		foreach($find as $k=>$v)
		{
			if(strpos($json->from, $v) !== FALSE)
				return TRUE;
		}
	}
	
	return FALSE;
}

function is_dev_agent($worker_sid)
{
	$CI = & get_instance();
	$CI->load->model('mdl_user');
	//$RG = $CI->mdl_user->get_usermeta(['users.id' => 31])->row();
	//$DV = $CI->mdl_user->get_usermeta(['users.id' => 44])->row();
	if(($worker_sid == 'WK66dd21202f4efdd41e90d5d6e04d7890' /*$RG->twilio_worker_id*/ || $worker_sid == 'WK16583a82c3139194665f75db7258ae31' /*$DV->twilio_worker_id*/) && $worker_sid)
		return TRUE;
	return FALSE;
}
