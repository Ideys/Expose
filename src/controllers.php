<?php

use Ideys\SilexHooks;
use Symfony\Component\HttpFoundation\Response;

$app->mount('/', include 'controllers/frontend/root.php');

$app->mount('/{_locale}', include 'controllers/frontend/showcase.php');

$app->mount('/{_locale}/profile', include 'controllers/frontend/profile.php');

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

    return new Response(SilexHooks::twig($app)->resolveTemplate('errors.html.twig')->render(array(
        'code' => $code,
    )), $code);
});
