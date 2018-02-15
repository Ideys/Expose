<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\FormServiceProvider;
Use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\TranslatorInterface;
use Ideys\ImagineServiceProvider\ImagineServiceProvider;
use Ideys\Settings\SettingsServiceProvider;
use Ideys\Content\Section\Provider\SectionProvider;

define('TABLE_PREFIX', $dbSettings['prefix']);

$app = new Application();
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new SecurityServiceProvider());
$app->register(new RememberMeServiceProvider());
$app->register(new DoctrineServiceProvider(), [
    'db.options' => [
        'driver' => $dbSettings['driver'],
        'host' => $dbSettings['host'],
        'port' => $dbSettings['port'],
        'dbname' => $dbSettings['dbname'],
        'socket' => $dbSettings['socket'],
        'user' => $dbSettings['user'],
        'password' => $dbSettings['password'],
    ]
]);
$app->register(new SwiftmailerServiceProvider());
$app->register(new TranslationServiceProvider());
$app->register(new ImagineServiceProvider());
$app->register(new SettingsServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider(), [
    'locale' => 'fr',
]);
$app->register(new Silex\Provider\AssetServiceProvider(), [
    'assets.version' => 'v1',
    'assets.version_format' => '%s?version=%s',
]);
$app->register(new TwigServiceProvider());

$app['route_class'] = 'Ideys\Route';
$app['translator'] = $app->extend('translator', function(TranslatorInterface $translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());

    foreach ($app['languages'] as $lg) {
        $translator->addResource('yaml', __DIR__.'/locales/'.$lg.'.yml', $lg);
    }

    return $translator;
});

$app['twig'] = $app->extend('twig', function(Twig_Environment $twig, $app) {

    // Global settings
    $twig->addGlobal('settings', $app['settings']->getSettings());
    $twig->addGlobal('profile', $app['session']->get('profile'));

    // Content sections (for menu)
    $sectionProvider = new SectionProvider($app);
    $twig->addGlobal('sections', $sectionProvider->findAll());
    $twig->addExtension(new Twig_Extension_StringLoader());

    return $twig;
});

$app->extend('twig.runtimes', function ($runtimes) {
    return array_merge($runtimes, [
        FormRenderer::class => 'twig.form.renderer',
    ]);
});

return $app;
