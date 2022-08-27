<?php

use application\core\Application;
use application\core\Bootstrap\BootProviders;
use application\core\Bootstrap\LoadConfiguration;
use application\core\Bootstrap\RegisterFacades;
use application\core\Bootstrap\RegisterProviders;
use application\core\Kernel;
use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel as KernelContract;

class InitDependencies
{

    /** @var Application $app */
    private $app;

    private $bootstrappers = [
        LoadConfiguration::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class
    ];

    public function init($params)
    {
        spl_autoload_register(function ($class) {
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            if (strpos($class, 'application' . DIRECTORY_SEPARATOR) !== false
                && file_exists(FCPATH . $class . '.php')) {
                require_once FCPATH . $class . '.php';
            }
        });

        Container::setInstance(new Application(FCPATH));

        app()->singleton(KernelContract::class, function ($app) {
            return new Kernel($app, $app['router']);
        });

        app(KernelContract::class)->bootstrap();
        app()->register(\application\core\ServiceProviders\CustomServiceProvider::class);
//        defined('DATE_FORMAT') OR define('DATE_FORMAT', config_item('dateFormat'));
//        defined('TIME_FORMAT') OR define('TIME_FORMAT', config_item('time') == 12 ? 'h:i:s a' : 'H:i:s');
//        defined('DATETIME_FORMAT') OR define('DATETIME_FORMAT', DATE_FORMAT.' '.TIME_FORMAT);
    }
}
