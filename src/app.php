<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Neutron\Silex\Provider\ImagineServiceProvider;

require __DIR__.'/util/Content.php';
require __DIR__.'/util/DynamicForm.php';
require __DIR__.'/util/Messaging.php';
require __DIR__.'/util/Settings.php';
require __DIR__.'/util/UserProvider.php';
require __DIR__.'/util/tools.php';

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new SecurityServiceProvider());
$app->register(new RememberMeServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new SwiftmailerServiceProvider());
$app->register(new TranslationServiceProvider());
$app->register(new ImagineServiceProvider());
$app->register(new TwigServiceProvider());
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());

    foreach ($app['languages'] as $lg) {
        $translator->addResource('yaml', __DIR__.'/locales/'.$lg.'.yml', $lg);
    }

    return $translator;
}));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {

    $settings = new Settings($app['db']);
    $twig->addGlobal('site', $settings->getAll());
    $content = new Content($app['db']);
    $twig->addGlobal('sections', $content->findSections());

    return $twig;
}));

return $app;
