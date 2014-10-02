<?php

use Ideys\SilexHooks;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->mount('/', include 'controllers/root.php');

$app->mount('/{_locale}', include 'controllers/frontend.php');

$app->mount('/admin/{_locale}/content', include 'controllers/backend/contentManager.php');

$app->mount('/admin/{_locale}/form', include 'controllers/backend/formManager.php');

$app->mount('/admin/{_locale}/gallery', include 'controllers/backend/galleryManager.php');

$app->mount('/admin/{_locale}/html', include 'controllers/backend/htmlManager.php');

$app->mount('/admin/{_locale}/blog', include 'controllers/backend/blogManager.php');

$app->mount('/admin/{_locale}/maps', include 'controllers/backend/mapManager.php');

$app->mount('/admin/{_locale}/channel', include 'controllers/backend/channelManager.php');

$app->mount('/admin/{_locale}/files', include 'controllers/backend/filesManager.php');

$app->mount('/admin/{_locale}/messaging', include 'controllers/backend/messagingManager.php');

$app->mount('/admin/{_locale}/settings', include 'controllers/backend/siteSettings.php');

$app->mount('/admin/{_locale}/users', include 'controllers/backend/usersManager.php');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return null;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response(SilexHooks::twig($app)->resolveTemplate($templates)->render(array('code' => $code)), $code);
});

/**
 * Guess client language, relies on browser data.
 *
 * @param array $app
 *
 * @return string
 */
function client_language_guesser($app) {
    $acceptLanguage = $app['request']->headers->get('accept-language');
    $userLanguage   = strtolower(substr($acceptLanguage, 0, 2));
    $language       = (in_array($userLanguage, $app['languages']))
                      ? $userLanguage : $app['locale_fallback'];
    return $language;
}
