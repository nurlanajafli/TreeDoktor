<?php

namespace application\core\ServiceProviders;

//use App\models\Relations\HasManySyncable;
use application\core\Application;
use application\core\Debugbar\SqlCollector;
use application\modules\user\models\User;
use Barryvdh\Debugbar\DataFormatter\QueryFormatter;
use Barryvdh\Debugbar\LaravelDebugbar;
use DebugBar\DataCollector\PDO\TraceablePDO;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

/**
 * Class CustomServiceProvider
 * @package application\core\ServiceProviders
 * @property Application $app
 */
class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app->extend(LengthAwarePaginator::class, function ($paginator, Container $container) {
            return new \application\core\Extenders\LengthAwarePaginator($paginator->items(), $paginator->total(),
                $paginator->perPage(), $paginator->currentPage(), $paginator->getOptions());
        });
        Paginator::currentPageResolver(function ($pageName = 'page') {
            $CI = &get_instance();
            $pageNameSegKey = array_search($pageName, $CI->uri->segment_array());
            $page = $CI->uri->segment($pageNameSegKey + 1);
            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int)$page >= 1) {
                return (int)$page;
            }

            return 1;
        });

//        $this->app->extend(HasMany::class, function (Builder $query, Model $parent, $foreignKey, $localKey) {
//            return new HasManySyncable($query, $parent, $foreignKey, $localKey);
//        });
    }

    public function boot()
    {
        if (config_item('debugbar') === true) {
            $this->addDebugBarCollector();
        }
        $this->setUser();
    }

    public function addDebugBarCollector()
    {
        if ($this->app->getProvider(\Barryvdh\Debugbar\ServiceProvider::class) != null) {
            /** @var LaravelDebugbar $debugbar */
            $debugbar = $this->app['debugbar'];
            $debugbar->enable();
            if ($debugbar->shouldCollect('sql', true)) {
                $CI = &get_instance();
                if ($debugbar->hasCollector('time') && $this->app['config']->get(
                        'debugbar.options.sql.timeline',
                        false
                    )
                ) {
                    $timeCollector = $debugbar->getCollector('time');
                } else {
                    $timeCollector = null;
                }
                $queryCollector = new SqlCollector(new TraceablePDO($this->app['db.connection']->getPdo()),
                    $timeCollector);

                $queryCollector->setDataFormatter(new QueryFormatter());

                if ($this->app['config']->get('debugbar.options.sql.with_params')) {
                    $queryCollector->setRenderSqlWithParams(true);
                }

                if ($this->app['config']->get('debugbar.options.sql.backtrace')) {
                    //$middleware = ! $this->is_lumen ? $this->app['router']->getMiddleware() : [];
                    $queryCollector->setFindSource(true, []);
                }

                if ($this->app['config']->get('debugbar.options.sql.explain.enabled')) {
                    $types = $this->app['config']->get('debugbar.options.sql.explain.types');
                    $queryCollector->setExplainSource(true, $types);
                }

                if ($this->app['config']->get('debugbar.options.sql.hints', true)) {
                    $queryCollector->setShowHints(true);
                }

                $debugbar->addCollector($queryCollector);

                try {
                    foreach ($CI->db->queries as $idx => $query) {
                        $queryCollector->addQuery((string)$query, [], $CI->db->query_times[$idx],
                            $this->app['db.connection']);
                    }

                } catch (\Exception $e) {
                    $debugbar->addThrowable(
                        new Exception(
                            'Cannot add listen to Queries for Laravel Debugbar: ' . $e->getMessage(),
                            $e->getCode(),
                            $e
                        )
                    );
                }
            }
        }
    }

    public function setUser()
    {
        if (isUserLoggedIn()) {
            $CI = &get_instance();
            $id = $CI->session->userdata('user_id');
            if ($user = User::find($id)) {
                $this->app['request']->setUserResolver(function () use ($user) {
                    return $user;
                });
            }
        } elseif ($user = getUserIfToken()) {
            $this->app['request']->setUserResolver(function () use ($user) {
                return $user;
            });
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'debugbar',
            'request'
        ];
    }
}
