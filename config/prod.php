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
        'pattern' => '^/admin',
        'form' => array(
            'login_path'  => '/login',
            'check_path'  => '/admin/login_check',
        ),
        'logout' => array(
            'logout_path' => '/admin/logout',
        ),
        'remember_me' => array(
            'key' => '2QRXS92PSXZ5SWGF5UB1LS901ZDPGYNNLG98H2BU',
            'always_remember_me' => true,
        ),
        'users' => $app->share(function () use ($app) {
            return new UserProvider($app['db']);
        }),
    ),
);

$app['security.role_hierarchy'] = array(
    'ROLE_SUPER_ADMIN' => array('ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'),
    'ROLE_ADMIN' => array('ROLE_USER'),
);

$app['security.access_rules'] = array(
    array('^/admin', 'ROLE_ADMIN'),
    array('^/private', 'ROLE_USER'),
);

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

$app['languages'] = array('en', 'fr');
$app['locale_fallback'] = 'fr';
$app['gallery.dir'] = WEB_DIR.'/gallery';
