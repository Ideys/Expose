<?php

require __DIR__.'/db.php';

$app['swiftmailer.options'] = array(
    'host' => 'localhost',
    'port' => '25',
    'username' => '',
    'password' => '',
    'encryption' => null,
    'auth_mode' => null
);

$app['security.firewalls'] = array(
    'website' => array(
        'anonymous' => true,
        'form' => array(
            'login_path'  => '/login',
            'check_path'  => '/login_check',
        ),
        'logout' => array(
            'logout_path' => '/logout',
        ),
        'remember_me' => array(
            'key' => '2QRXS92PSXZ5SWGF5UB1LS901ZDPGYNNLG98H2BU',
            'always_remember_me' => true,
        ),
        'users' => $app->share(function () use ($app) {
            return new \Ideys\User\UserProvider($app['db'], $app['session']);
        }),
    ),
);

$app['security.role_hierarchy'] = array(
    'ROLE_SUPER_ADMIN' => array('ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'),
    'ROLE_ADMIN' => array('ROLE_EDITOR'),
    'ROLE_EDITOR' => array('ROLE_USER'),
);

$app['security.access_rules'] = array(
    array('^/admin/', 'ROLE_EDITOR'),
);

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

$app['languages'] = array('en', 'fr');
$app['locale_fallback'] = 'fr';

$app['semver'] = '0.20';
