<?php


class MY_URI extends CI_URI
{
    var $disabledSegment = 'iframe';
	var $foundDisabled = FALSE;

//	function __construct()
//	{
//		$this->config =& load_class('Config', 'core');
//		log_message('debug', "URI Class Initialized");
//	}

	function segment($n, $no_result = FALSE)
	{
		$segments = $this->segments;
		$this->segments = array();
		$count = 0;
		foreach($segments as $key => $segment)
		{
            if ($segment != $this->disabledSegment)
				$this->segments[++$count] = $segment;
			else
				$this->foundDisabled = TRUE;
		}
		return (!isset($this->segments[$n])) ? $no_result : $this->segments[$n];
	}
} 