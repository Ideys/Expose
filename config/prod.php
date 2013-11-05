<?php

$app['db.options'] = array(
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'port' => null,
    'dbname' => 'exposedb',
    'user' => 'root',
    'password' => null,
);

$app['swiftmailer.options'] = array(
    'host' => 'localhost',
    'port' => '25',
    'username' => '',
    'password' => '',
    'encryption' => null,
    'auth_mode' => null
);

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

$app['languages'] = array('en', 'fr');
$app['locale_fallback'] = 'fr';
