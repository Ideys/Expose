<?php

use Ideys\SilexHooks;
use Ideys\Seo\SitemapManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$rootController = SilexHooks::controllerFactory($app);

$rootController->get('/', function (Request $request) use ($app) {

    $language = SilexHooks::settingsManager($app)->clientLanguageSelector($request);

    return SilexHooks::redirect($app, 'homepage', array('_locale' => $language));
})
->bind('root')
;

$rootController->get('/sitemap.xml', function () use ($app) {

    $sitemapManager = new SitemapManager($app['db'], $app['url_generator'], $app['settings']);
    $urls = $sitemapManager->generateSitemapData();

    return new Response($app['twig']->render('frontend/sitemap.xml.twig', array(
        'urls' => $urls
    )), 200, array('Content-Type' => 'application/xml'));
 })
->bind('sitemap')
;

$rootController->get('/admin', function (Request $request) use ($app) {

    $language = SilexHooks::settingsManager($app)->clientLanguageSelector($request);

    return SilexHooks::redirect($app, 'admin_content_manager', array('_locale' => $language));
})
->bind('admin')
;

$rootController->get('/admin-redirect', function (Request $request) use ($app) {

    $language = SilexHooks::settingsManager($app)->clientLanguageSelector($request);
    $security = $app['security.voters'];

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
