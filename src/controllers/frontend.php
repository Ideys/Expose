<?php

$frontendController = $app['controllers_factory'];

$frontendController->get('/', function () use ($app) {

    return $app['twig']->render('frontend/homepage.html.twig');
})
->bind('homepage')
;

$frontendController->get('/theme/{slug}', function ($slug) use ($app) {

    $gallery = new Gallery($app);
    $section = $gallery->findSection($slug);

    return $app['twig']->render('frontend/'.$section['type'].'.html.twig', array(
      'section' => $section,
    ));
})
->bind('section')
;

$frontendController->get('/contact', function () use ($app) {

    return $app['twig']->render('frontend/contact.html.twig');
})
->bind('contact')
;

$frontendController->assert('_locale', implode('|', $app['languages']));

return $frontendController;
