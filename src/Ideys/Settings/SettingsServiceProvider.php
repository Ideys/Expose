<?php

namespace Ideys\Settings;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Silex settings manager service provider for Ideys Expose.
 */
class SettingsServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['settings'] = new SettingsManager($app['db']);
    }
}
