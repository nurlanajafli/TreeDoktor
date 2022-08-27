<?php

namespace application\core\Bootstrap;

use application\core\Application;

class RegisterProviders
{
    /**
     * Bootstrap the given application.
     *
     * @param Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->registerConfiguredProviders();
    }
}
