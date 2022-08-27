<?php


class MY_Config extends CI_Config
{

    var $config = array();
    var $ci = array();
    var $is_loaded = array();
    var $_config_paths = array(APPPATH);

    function __construct()
    {
        $this->config =& get_config();
        log_message('debug', "Config Class Initialized");

        if (php_sapi_name() == "cli") {
            include(APPPATH.'config/company.php'); //TODO: think about base_url in CLI mode
            if(isset($config['wsClient']) && $config['wsClient'])
                $this->set_item('base_url', preg_replace('/:[0-9]{1,5}/is', '', $config['wsClient']) . '/');
        }

        else {
            // Set the base_url automatically if none was provided
            if ($this->config['base_url'] == '')
            {
                if (isset($_SERVER['HTTP_HOST']))
                {
                    $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
                    $base_url .= '://'. $_SERVER['HTTP_HOST'];
                    $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
                }

                $this->set_item('base_url', $base_url);
            }
        }
    }
}
