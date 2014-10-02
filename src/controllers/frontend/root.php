<?php

use Ideys\SilexHooks;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception;

$rootController = SilexHooks::controllerFactory($app);

$rootController->get('/', function () use ($app) {

    $language = client_language_guesser($app);

    return SilexHooks::redirect($app, 'homepage', array('_locale' => $language));
})
->bind('root')
;

$rootController->get('/admin', function () use ($app) {

    $language = client_language_guesser($app);

    return SilexHooks::redirect($app, 'admin_content_manager', array('_locale' => $language));
})
->bind('admin')
;

$rootController->get('/admin-redirect', function () use ($app) {

    $language = client_language_guesser($app);
    $security = SilexHooks::security($app);

    if ($security->isGranted('ROLE_EDITOR')) {
        $redirectRoute = 'admin_content_manager';
    } elseif ($security->isGranted('ROLE_USER')) {
        $redirectRoute = 'user_profile';
    } else {
        $redirectRoute = 'login';
    }

    return SilexHooks::redirect($app, $redirectRoute, array('_locale' => $language));
})
->bind('admin_redirection')
;

$rootController->get('/login', function(Request $request) use ($app) {
    return SilexHooks::twig($app)->render('backend/login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => SilexHooks::session($app)->get('_security.last_username'),
    ));
})
->bind('login')
;

$rootController->assert('_locale', implode('|', $app['languages']));

return $rootController;
