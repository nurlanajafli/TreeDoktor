<?php


use application\core\Application;
use application\core\Console\Application as ConsoleApplication;
use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Support\Str;

/**
 * Class Console
 * @property Application $container
 * @property ConsoleApplication $app
 */
class Console extends MX_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!is_cli()) {
            die('allowed only in cli mode!');
        }

        app()->register(\application\core\ServiceProviders\ConsoleServiceProvider::class);
        app()->register(\Illuminate\Database\MigrationServiceProvider::class);
        app()->register(\application\core\ServiceProviders\ComposerServiceProvider::class);
        $this->registerAliases();
        $this->registerDefaultCommands();
        $this->registerCommands();
    }

    public function index()
    {
        app('console')->run();
    }

    private function registerCommands()
    {
        $files = scandir(APPPATH . 'commands', SCANDIR_SORT_NONE);
        foreach ($files as $file) {
            if ($file === '.' || $file === "..") {
                continue;
            }
            $class = pathinfo($file, PATHINFO_FILENAME);
            if (!Str::endsWith(strtolower($class), 'command')) {
                continue;
            }
            //require_once APPPATH . 'console' . DIRECTORY_SEPARATOR . $file;
            $class = '\application\commands\\' . $class;
            app('console')->resolve($class);
        }
    }

    private function registerDefaultCommands()
    {
        app()->singleton(
            'command.ide-helper.models',
            function ($app) {
                return new ModelsCommand($app['files']);
            }
        );
        app()->mergeConfigFrom(config_path('ide-helper.php'), 'ide-helper');

        //app('db')->connection()->setPdo(\application\core\Database\ConnectToPdo::connect());

        $commands = [
            'command.ide-helper.models',
        ];
        app('console')->resolveCommands($commands);
    }

    private function registerAliases()
    {
        app()->alias('migration.repository', DatabaseMigrationRepository::class);
        app()->alias('migration.repository', MigrationRepositoryInterface::class);
        app()->alias('migration.creator', MigrationCreator::class);
    }
}
