<?php

namespace application\core\Bootstrap;

use application\core\Application;

class BootProviders
{
    /**
     * Bootstrap the given application.
     *
     * @param \application\core\Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->boot();
    }
}
