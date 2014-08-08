<?php

use Ideys\SilexHooks;
use Ideys\Messaging\Messaging;

$messagingManagerController = SilexHooks::controllerFactory($app);

$messagingManagerController->get('/', function () use ($app) {

    $messaging = new Messaging($app['db']);
    $messages = $messaging->findAll();
    $count = $messaging->countAll();

    return SilexHooks::twig($app)->render('backend/messagingManager/messagingList.html.twig', array(
        'messages' => $messages,
        'count' => $count,
        'is_archive' => false,
    ));
})
->bind('admin_messaging_manager')
;

$messagingManagerController->get('/archive', function () use ($app) {

    $messaging = new Messaging($app['db']);
    $messages = $messaging->findArchived();
    $count = $messaging->countAll();

    return SilexHooks::twig($app)->render('backend/messagingManager/messagingList.html.twig', array(
        'messages' => $messages,
        'count' => $count,
        'is_archive' => true,
    ));
})
->bind('admin_messaging_manager_archive')
;

$messagingManagerController->get('/new-counter', function () use ($app) {

    $messaging = new Messaging($app['db']);
    return $app->json($messaging->countUnread());
})
->bind('admin_messaging_manager_new_messages_counter')
;

$messagingManagerController->post('/{id}/mark-as-read', function ($id) use ($app) {

    $messaging = new Messaging($app['db']);
    $messaging->markAsRead($id);

    return $app->json(true);
})
->assert('id', '\d+')
->bind('admin_messaging_manager_mark_as_read')
;

$messagingManagerController->get('/{id}/archive', function ($id) use ($app) {

    $messaging = new Messaging($app['db']);
    $messaging->archive($id);

    return SilexHooks::redirect($app, 'admin_messaging_manager');
})
->assert('id', '\d+')
->bind('admin_messaging_manager_archive_item')
;

$messagingManagerController->get('/{id}/delete', function ($id) use ($app) {

    $messaging = new Messaging($app['db']);
    $messaging->delete($id);

    return SilexHooks::redirect($app, 'admin_messaging_manager');
})
->assert('id', '\d+')
->bind('admin_messaging_manager_delete')
;

$messagingManagerController
->assert('_locale', implode('|', $app['languages']))
->secure('ROLE_ADMIN')
;

return $messagingManagerController;
