<?php

namespace application\core\ServiceProviders;

use application\core\LengthAwarePaginator;
use Illuminate\Support\ServiceProvider;

class PaginationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom($this->app->resourcePath('views' . DIRECTORY_SEPARATOR . 'pagination'), 'pagination');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/resources/views' => $this->app->resourcePath('views/vendor/pagination'),
            ], 'laravel-pagination');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        LengthAwarePaginator::viewFactoryResolver(function () {
            return $this->app['view'];
        });

        LengthAwarePaginator::currentPathResolver(function () {
            return $this->app['request']->url();
        });

        LengthAwarePaginator::currentPageResolver(function ($pageName = 'page') {
            $CI = &get_instance();
            $pageNameSegKey = array_search($pageName, $CI->uri->segment_array());
            $page = $CI->uri->segment($pageNameSegKey + 1);
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int)$page >= 1) {
                return (int)$page;
            }

            return 1;
        });

        LengthAwarePaginator::queryStringResolver(function () {
            return $this->app['request']->query();
        });
    }
}
