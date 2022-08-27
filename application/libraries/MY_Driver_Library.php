<?php

/**
 * Class MY_Driver_Library
 */
class MY_Driver_Library extends CI_Driver_Library
{
    protected $valid_drivers;
    /** @var CI_Controller $CI */
    public $CI;
    protected $_adapter;
    public $enabled = false;

    public function __construct($config)
    {
        $this->CI =& get_instance();
    }

    /**
     * @return bool
     */
    public function enabled()
    {
        return $this->enabled;
    }

    /**
     * @param string $adapter Adapter name
     * @return MY_Driver_Library
     */
    public function setAdapter($adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * @return string Adapter name
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    public function validateAdapter($adapter) {
        if ( ! isset($this->lib_name))
            $this->lib_name = get_class($this);
        $child_class = $adapter;
        $driver_name = strtolower(str_replace(['CI_', '/'], ['', DIRECTORY_SEPARATOR], $child_class));
        if (!in_array($driver_name, array_map('strtolower', $this->valid_drivers)))
            return false;
        return true;
    }

    // The first time a child is used it won't exist, so we instantiate it
    // subsequents calls will go straight to the proper child.
    function __get($child)
    {
        if ( ! isset($this->lib_name))
        {
            $this->lib_name = get_class($this);
        }

        // The class will be prefixed with the parent lib
        $child_class = $child;

        // Remove the CI_ prefix and lowercase
        $lib_name = ucfirst(strtolower(str_replace('CI_', '', $this->lib_name)));
        $driver_name = strtolower(str_replace(['CI_', '/'], ['', DIRECTORY_SEPARATOR], $child_class));

        if (in_array($driver_name, array_map('strtolower', $this->valid_drivers)))
        {
            // check and see if the driver is in a separate file
            if ( ! class_exists($child_class))
            {
                // check application path first
                foreach (get_instance()->load->get_package_paths(TRUE) as $path)
                {
                    // loves me some nesting!
                    foreach (array(ucfirst($driver_name), $driver_name) as $class)
                    {
                        $filepath = $path.'libraries/'.$lib_name.'/drivers/'.$class.'.php';

                        if (file_exists($filepath))
                        {
                            include_once $filepath;
                            break;
                        }
                    }
                }
                $className = basename($child_class);
                // it's a valid driver, but the file simply can't be found
                if ( ! class_exists($className))
                {
                    log_message('error', "Unable to load the requested driver: ".$className);
                    show_error("Unable to load the requested driver: ".$className);
                }
            }

            $obj = new $className;
            $obj->decorate($this);
            if(method_exists($obj, 'init')){
                $obj->init();
            }
            $this->$child = $obj;
            return $this->$child;
        }

        // The requested driver isn't valid!
        log_message('error', "Invalid driver requested: ".$child_class);
        show_error("Invalid driver requested: ".$child_class);
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->{$this->_adapter}, $method), $args);
    }
}
