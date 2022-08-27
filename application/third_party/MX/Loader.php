<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Modular Extensions - HMVC
 *
 * Adapted from the CodeIgniter Core Classes
 * @link	http://codeigniter.com
 *
 * Description:
 * This library extends the CodeIgniter CI_Loader class
 * and adds features allowing use of modules and the HMVC design pattern.
 *
 * Install this file as application/third_party/MX/Loader.php
 *
 * @copyright    Copyright (c) 2015 Wiredesignz
 * @version    5.5
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/
class MX_Loader extends CI_Loader
{
	protected $_module;

	public $_ci_plugins = array();
	public $_ci_cached_vars = array();
    protected $_ci_unloaded_classes = array();
    protected $renderer;

	/** Initialize the loader variables **/
    public function initialize($controller = null)
    {
		/* set the module name */
		$this->_module = CI::$APP->router->fetch_module();

        if ($controller instanceof MX_Controller) {
			/* reference to the module controller */
			$this->controller = $controller;

			/* references to ci loader variables */
            foreach (get_class_vars('CI_Loader') as $var => $val) {
                if ($var != '_ci_ob_level') {
					$this->$var =& CI::$APP->load->$var;
				}
			}
        } else {
			parent::initialize();

			/* autoload module items */
			$this->_autoloader(array());
		}

		/* add this module path to the loader variables */
		$this->_add_module_paths($this->_module);
	}

	/** Add a module path loader variables **/
    public function _add_module_paths($module = '')
    {
		if (empty($module)) return;

        foreach (Modules::$locations as $location => $offset) {
			/* only add a module path if it exists */
            if (is_dir($module_path = $location . $module . '/') && !in_array($module_path, $this->_ci_model_paths))
			{
				array_unshift($this->_ci_model_paths, $module_path);
			}
		}
    }

	/** Load a module config file **/
    public function config($file, $use_sections = false, $fail_gracefully = false)
    {
		return CI::$APP->config->load($file, $use_sections, $fail_gracefully, $this->_module);
	}

    /**
     * Database Loader
     *
     * @param mixed $params Database configuration options
     * @param bool $return Whether to return the database object
     * @param bool $query_builder Whether to enable Query Builder
     *                    (overrides the configuration setting)
     *
     * @return    object|bool    Database object if $return is set to TRUE,
     *                    FALSE on failure, CI_Loader instance in any other case
     */
    function database($params = '', $return = false, $query_builder = null)
    {
        // Grab the super object
        $CI =& get_instance();

        // Do we even need to load the database class?
        if ($return === false && $query_builder === null && isset($CI->db) && is_object($CI->db) && !empty($CI->db->conn_id)) {
            return false;
        }

        require_once(BASEPATH . 'database/DB.php');

        // Load the DB class
        $db =& DB($params, $query_builder);

        $db->conn_id->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $my_driver = config_item('subclass_prefix') . 'DB_' . $db->dbdriver . '_driver';
        $my_driver_file = APPPATH . 'core/' . $my_driver . EXT;

        if (file_exists($my_driver_file)) {
            require_once($my_driver_file);
            $db = new $my_driver(get_object_vars($db));
        }

        if ($return === true) {
            return $db;
        }

        // Initialize the db variable.  Needed to prevent
        // reference errors with some configurations
        $CI->db = '';
        $CI->db = $db;

        // use our mock PDO class if PDO is not enabled on this server
        if (!class_exists('PDO')) {
            class_alias('Illuminate\CodeIgniter\FakePDO', 'PDO');
        }

        return $this;
    }

//	/** Load a module helper **/
//    public function helper($helper = array())
//    {
//		if (is_array($helper)) return $this->helpers($helper);
//
//		if (isset($this->_ci_helpers[$helper]))	return;
//
//		list($path, $_helper) = Modules::find($helper.'_helper', $this->_module, 'helpers/');
//
//		if ($path === FALSE) return parent::helper($helper);
//
//		Modules::load_file($_helper, $path);
//		$this->_ci_helpers[$_helper] = TRUE;
//        return $this;
//	}
//
//	/** Load an array of helpers **/
//    public function helpers($helpers = array())
//    {
//        foreach ($helpers as $_helper) {
//            $this->helper($_helper);
//        }
//        return $this;
//	}

//	/** Load a module language file **/
//    public function language($langfile, $idiom = '', $return = false, $add_suffix = true, $alt_path = '')
//    {
//        CI::$APP->lang->load($langfile, $idiom, $return, $add_suffix, $alt_path, $this->_module);
//        return $this;
//    }
//
//    public function languages($languages)
//	{
//		foreach($languages as $_language) $this->language($_language);
//        return $this;
//	}

	/** Load a module library **/
//    public function library($library, $params = null, $object_name = null)
//    {
//        if (is_array($library)) return $this->libraries($library);
//
//		$class = strtolower(basename($library));
//
//        if (isset($this->_ci_classes[$class]) && $_alias = $this->_ci_classes[$class])
//            return $this;
//
//		($_alias = strtolower($object_name)) OR $_alias = $class;
//
//		list($path, $_library) = Modules::find($library, $this->_module, 'libraries/');
//
//		/* load library config file as params */
//        if ($params == null) {
//            list($path2, $file) = Modules::find($_alias, $this->_module, 'config/');
//            ($path2) && $params = Modules::load_file($file, $path2, 'config');
//        }
//
//        if ($path === false) {
//            $this->_ci_load_library($library, $params, $object_name);
//        } else
//		{
//			Modules::load_file($_library, $path);
//
//			$library = ucfirst($_library);
//			CI::$APP->$_alias = new $library($params);
//
//			$this->_ci_classes[$class] = $_alias;
//        }
//        return $this;
//    }

	/** Load an array of libraries **/
    public function libraries($libraries)
    {
        foreach ($libraries as $library => $alias) {
            (is_int($library)) ? $this->library($alias) : $this->library($library, null, $alias);
        }
        return $this;
	}

//	/** Load a module model **/
//    public function model($model, $object_name = null, $connect = false)
//	{
//		if (is_array($model)) return $this->models($model);
//
//		($_alias = $object_name) OR $_alias = basename($model);
//
//        if (in_array($_alias, $this->_ci_models, true))
//            return $this;
//
//		/* check module */
//		list($path, $_model) = Modules::find(strtolower($model), $this->_module, 'models/');
//
//        if ($path == false)
//		{
//			/* check application & packages */
//			parent::model($model, $object_name, $connect);
//        } else
//		{
//			class_exists('CI_Model', FALSE) OR load_class('Model', 'core');
//
//            if ($connect !== false && !class_exists('CI_DB', false)) {
//				if ($connect === TRUE) $connect = '';
//				$this->database($connect, FALSE, TRUE);
//			}
//
//			Modules::load_file($_model, $path);
//
//			$model = ucfirst($_model);
//			CI::$APP->$_alias = new $model();
//
//			$this->_ci_models[] = $_alias;
//        }
//        return $this;
//	}

	/** Load an array of models **/
    public function models($models)
    {
        foreach ($models as $model => $alias) {
            (is_int($model)) ? $this->model($alias) : $this->model($model, $alias);
        }
        return $this;
	}

	/** Load a module controller **/
    public function module($module, $params = null)
	{
		if (is_array($module)) return $this->modules($module);

		$_alias = strtolower(basename($module));
		CI::$APP->$_alias = Modules::load(array($module => $params));
        return $this;
	}

	/** Load an array of controllers **/
    public function modules($modules)
    {
        foreach ($modules as $_module) {
            $this->module($_module);
        }
        return $this;
	}

//	/** Load a module plugin **/
//    public function plugin($plugin)
//    {
//        if (is_array($plugin)) {
//            return $this->plugins($plugin);
//        }
//
//        if (isset($this->_ci_plugins[$plugin])) {
//            return $this;
//        }
//
//        list($path, $_plugin) = Modules::find($plugin . '_pi', $this->_module, 'plugins/');
//
//        if ($path === false && !is_file($_plugin = APPPATH . 'plugins/' . $_plugin . EXT))
//		{
//			show_error("Unable to locate the plugin file: {$_plugin}");
//		}
//
//		Modules::load_file($_plugin, $path);
//		$this->_ci_plugins[$plugin] = true;
//        return $this;
//	}

//	/** Load an array of plugins **/
//    public function plugins($plugins)
//    {
//        foreach ($plugins as $_plugin) {
//            $this->plugin($_plugin);
//        }
//        return $this;
//	}

    /**
     * Driver Loader
     *
     * Loads a driver library.
     *
     * @param string|string[] $library Driver name(s)
     * @param array $params Optional parameters to pass to the driver
     * @param string $object_name An optional object name to assign to
     *
     * @return    object|bool    Object or FALSE on failure if $library is a string
     *                and $object_name is set. CI_Loader instance otherwise.
     */
    public function driver($library, $params = null, $object_name = null)
    {
        if (is_array($library)) {
            foreach ($library as $key => $value) {
                if (is_int($key)) {
                    $this->driver($value, $params);
                } else {
                    $this->driver($key, $params, $value);
                }
            }

            return $this;
        } elseif (empty($library)) {
            return false;
        }

        if (!class_exists('CI_Driver_Library', false)) {
            // We aren't instantiating an object here, just making the base class available
            require BASEPATH . 'libraries/Driver.php';
            require APPPATH . 'libraries/MY_Driver.php';
            require APPPATH . 'libraries/MY_Driver_Library.php';
        }

        // We can save the loader some time since Drivers will *always* be in a subfolder,
        // and typically identically named to the library
        if (!strpos($library, '/')) {
            $library = ucfirst($library) . '/' . $library;
        }

        return $this->library($library, $params, $object_name);
    }

	/** Load a module view **/
    public function view($view, $vars = array(), $return = FALSE)
	{
        if (config_item('debugbar') === true) {
            if (in_array($view, ['includes/header', 'includes/footer'])) {
                if (!$this->renderer) {
                    app('debugbar')->enable();
                    $this->renderer = app('debugbar')->getJavascriptRenderer();
                    $this->renderer->setOpenHandlerUrl('/_debugbar/handle');
                }
                $vars['renderer'] = $this->renderer;
            }
        }

		list($path, $_view) = Modules::find($view, $this->_module, 'views/');

        if ($path != FALSE)
		{
			$this->_ci_view_paths = array($path => TRUE) + $this->_ci_view_paths;
			$view = $_view;
		}

		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
    }

    /** Load a module view handlebars **/
    public function view_hb($view, $return = false)
    {

        list($path, $_view) = Modules::find($view, $this->_module, 'views/templates/', '.hbs');

        if ($path != false) {
            $this->_ci_view_paths = array($path => true) + $this->_ci_view_paths;
            $view = $_view;
        }

        return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => [], '_ci_return' => $return), '.hbs');
    }

    function view_xml($view, $vars = array(), $return = false)
    {
        $CI = &get_instance();

        list($path, $_view) = Modules::find($view, $this->_module, 'views/');
        header('Content-Type: application/xml; charset=utf-8');
        CI::$APP->output->append_output('<?xml version="1.0" encoding="UTF-8"?>' . "\n");

        if ($path != false) {
            $this->_ci_view_paths = array($path => true) + $this->_ci_view_paths;
            $view = $_view;
        }

        $CI->output->set_content_type('text/xml');
        return $this->_ci_load(array(
            '_ci_view' => $view,
            '_ci_vars' => $this->_ci_object_to_array($vars),
            '_ci_return' => $return
        ));
    }

    public function ext_view($view, $vars = array(), $return = false)
    {
        $_ci_ext = pathinfo($view, PATHINFO_EXTENSION);
        $view = ($_ci_ext == '') ? $view . '.php' : $view;
        return $this->_ci_load(array(
                '_ci_vars' => $this->_ci_object_to_array($vars),
                '_ci_path' => $view,
                '_ci_return' => $return
            )
        );

    }

    protected function &_ci_get_component($component)
	{
		return CI::$APP->$component;
    }

    public function __get($class)
	{
		return (isset($this->controller)) ? $this->controller->$class : CI::$APP->$class;
    }

    public function _ci_load($_ci_data, $ext = EXT)
	{
		extract($_ci_data);

        if (isset($_ci_view))
		{
			$_ci_path = '';

			/* add file extension if not provided */
            $_ci_file = (pathinfo($_ci_view, PATHINFO_EXTENSION)) ? $_ci_view : $_ci_view . $ext;

            foreach ($this->_ci_view_paths as $path => $cascade) {
                if (file_exists($view = $path . $_ci_file))
				{
					$_ci_path = $view;
					break;
				}
				if ( ! $cascade) break;
            }
        } elseif (isset($_ci_path))
		{

			$_ci_file = basename($_ci_path);
			if( ! file_exists($_ci_path)) $_ci_path = '';
        }

        if (empty($_ci_path))
			show_error('Unable to load the requested file: '.$_ci_file);

        if (isset($_ci_vars))
			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, (array) $_ci_vars);

		extract($this->_ci_cached_vars);

		ob_start();

        if ((bool)@ini_get('short_open_tag') === false && CI::$APP->config->item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
        } else {
            include($_ci_path);
		}

		log_message('debug', 'File loaded: '.$_ci_path);

		if ($_ci_return == TRUE) return ob_get_clean();

        if (ob_get_level() > $this->_ci_ob_level + 1)
		{
			ob_end_flush();
        }
		else
		{
			CI::$APP->output->append_output(ob_get_clean());
		}
	}

	/** Autoload module items **/
    public function _autoloader($autoload)
	{
		$path = false;

        if ($this->_module) {
            list($path, $file) = Modules::find('constants', $this->_module, 'config/');

			/* module constants file */
            if ($path != FALSE)
			{
				include_once $path.$file.EXT;
			}

			list($path, $file) = Modules::find('autoload', $this->_module, 'config/');

			/* module autoload file */
            if ($path != FALSE)
			{
				$autoload = array_merge(Modules::load_file($file, $path, 'autoload'), $autoload);
			}
		}

		/* nothing to do */
        if (count($autoload) == 0) return;

		/* autoload package paths */
        if (isset($autoload['packages'])) {
            foreach ($autoload['packages'] as $package_path)
			{
				$this->add_package_path($package_path);
			}
		}

		/* autoload config */
        if (isset($autoload['config'])) {
            foreach ($autoload['config'] as $config)
			{
				$this->config($config);
			}
		}

		/* autoload helpers, plugins, languages */
        foreach (array('helper', 'plugin', 'language') as $type) {
            if (isset($autoload[$type])) {
                foreach ($autoload[$type] as $item)
				{
					$this->$type($item);
                }
            }
        }

        // Autoload drivers
        if (isset($autoload['drivers'])) {
            foreach ($autoload['drivers'] as $item => $alias) {
                (is_int($item)) ? $this->driver($alias) : $this->driver($item, $alias);
		    }
		}

		/* autoload database & libraries */
        if (isset($autoload['libraries'])) {
            if (in_array('database', $autoload['libraries']))
			{
				/* autoload database */
                if (!$db = CI::$APP->config->item('database')) {
                    $this->database();
                    $autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
				}
			}

			/* autoload libraries */
            foreach ($autoload['libraries'] as $library => $alias) {
                (is_int($library)) ? $this->library($alias) : $this->library($library, NULL, $alias);
			}
		}

		/* autoload models */
        if (isset($autoload['model'])) {
            foreach ($autoload['model'] as $model => $alias) {
                (is_int($model)) ? $this->model($alias) : $this->model($model, $alias);
			}
		}

		/* autoload module controllers */
        if (isset($autoload['modules'])) {
            foreach ($autoload['modules'] as $controller) {
                ($controller != $this->_module) && $this->module($controller);
			}
		}

        // Autoload drivers
        if (isset($autoload['drivers'])) {
            // Load all other libraries
            foreach ($autoload['drivers'] as $item) {
                $this->driver($item);
            }
        }
    }

    public function unload($class)
    {
        if (isset($this->_ci_classes[$class])) {
            unset($this->_ci_classes[$class]);
        }
        $CI =& get_instance();
        unset($CI->{$class});
        $this->_ci_unloaded_classes[strtolower($class)] = true;
        return true;
    }

    /**
     * CI Object to Array translator
     *
     * Takes an object as input and converts the class variables to
     * an associative array with key/value pairs.
     *
     * @param object $object Object data to translate
     * @return    array
     */
    public function _ci_object_to_array($object)
    {
        return is_object($object) ? get_object_vars($object) : $object;
    }

    public function unload_model($modelName) {
        if(isset($this->_ci_models[$modelName])) {
            unset($this->_ci_models[$modelName]);
        }
    }

    public function get_loaded_models() {
        return $this->_ci_models;
    }

}

/** load the CI class for Modular Separation **/
(class_exists('CI', FALSE)) OR require dirname(__FILE__).'/Ci.php';