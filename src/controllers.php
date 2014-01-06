<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {

    $language = client_language_guesser($app);

    return $app->redirect($app['url_generator']->generate('homepage', array('_locale' => $language)));
})
->bind('root')
;

$app->get('/admin', function () use ($app) {

    $language = client_language_guesser($app);

    return $app->redirect($app['url_generator']->generate('admin_content_manager', array('_locale' => $language)));
})
->bind('admin')
;

$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('backend/login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})
->bind('admin_login')
;

$app->mount('/{_locale}', include 'controllers/frontend.php');

$app->mount('/admin/{_locale}/content', include 'controllers/backend/contentManager.php');

$app->mount('/admin/{_locale}/form', include 'controllers/backend/formManager.php');

$app->mount('/admin/{_locale}/gallery', include 'controllers/backend/galleryManager.php');

$app->mount('/admin/{_locale}/page', include 'controllers/backend/pageManager.php');

$app->mount('/admin/{_locale}/messaging', include 'controllers/backend/messagingManager.php');

$app->mount('/admin/{_locale}/settings', include 'controllers/backend/siteSettings.php');

$app->mount('/admin/{_locale}/users', include 'controllers/backend/usersManager.php');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
