<?php

$backendController = $app['controllers_factory'];

$backendController->get('/', function () use ($app) {

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->bind('admin')
;

$backendController->get('/logout', function () use ($app) {

    $app['session']->remove('user');
    return $app->redirect($app['url_generator']->generate('homepage'));
})
->bind('admin_logout')
;

$backendController->assert('_locale', implode('|', $app['languages']));

return $backendController;
