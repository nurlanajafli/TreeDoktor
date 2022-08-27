<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
interface_exists('GpsInterface', FALSE) OR require_once(APPPATH . '/libraries/Gps/GpsInterface.php');
trait_exists('MY_Driver_Credentials_trait',FALSE) OR require_once(APPPATH . 'libraries/MY_Driver_Credentials_trait.php');

/**
 * GPS Class
 */
class Gps extends MY_Driver_Library
{
    use MY_Driver_Credentials_trait;
    public $CI;
    protected $_adapter = 'genuinetrackingsolutions';
    public $cookieFile;

    /**
     * Class constructor
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $this->enabled = config_item('gps_enabled');//$config['gps_enabled'];
        $this->valid_drivers = $config['gps_services'];
        $this->_adapter = $config['gps_default'];
        $this->credentials = $config['gps_credentials'];
        $this->cookieFile = str_replace('//','/',$config['gps_cookiedir'] . strtolower('/gps_' . $this->_adapter . '_' . $this->getCredentials('login') . '_cookie.txt'));

    }

}
