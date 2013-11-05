<?php

$frontendController = $app['controllers_factory'];

$frontendController->get('/', function () use ($app) {

    return $app['twig']->render('frontend/homepage.html.twig');
})
->bind('homepage')
;

$frontendController->assert('_locale', implode('|', $app['languages']));

return $frontendController;
