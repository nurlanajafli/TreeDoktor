<?php

namespace application\core\ServiceProviders;

use application\core\Application;
use application\core\Console\Application as ConsoleApplication;
use application\core\Console\Command;
use Illuminate\Console\Command as IlluminateCommand;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;


class ConsoleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend(IlluminateCommand::class, function ($client, Container $container) {
            return new Command();
        });

        $this->app->singleton('console', function ($app) {
            return new ConsoleApplication($app, app('events'), Application::VERSION);
        });
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'console'
        ];
    }
}
