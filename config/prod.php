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

$app['security.firewalls'] = array(
    'admin' => array(
        'pattern' => '^/[a-z]{2}/admin',
        'http' => true,
        'remember_me' => array(
            'key' => '2QRXS92PSXZ5SWGF5UB1LS901ZDPGYNNLG98H2BU',
            'always_remember_me' => true,
        ),
        'users' => $app->share(function () use ($app) {
            return new UserProvider($app['db']);
        }),
    ),
);

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

$app['languages'] = array('en', 'fr');
$app['locale_fallback'] = 'fr';
$app['gallery.dir'] = WEB_DIR.'/gallery';
