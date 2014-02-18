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

foreach (glob(__DIR__.'/models/*.php') as $filename) {
    require $filename;
}

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

    $settings = new \Ideys\Settings($app['db']);
    $twig->addGlobal('semver', '0.7.5');
    $twig->addGlobal('site', $settings->getAll());
    $twig->addGlobal('profile', $app['session']->get('profile'));
    $contentFactory = new \Ideys\Content\ContentFactory($app);
    $twig->addGlobal('sections', $contentFactory->findSections());
    $twig->addExtension(new Twig_Extension_StringLoader());

    return $twig;
}));

return $app;
