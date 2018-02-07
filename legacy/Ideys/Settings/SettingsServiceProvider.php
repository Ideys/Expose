<?php

namespace Ideys\Settings;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Silex settings manager service provider for Ideys Expose.
 */
class SettingsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['settings'] = $app->share(function ($app) {
            return new SettingsManager($app['db']);
        });
    }

    public function boot(Application $app)
    {
    }
}
