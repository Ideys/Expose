<?php

$messagingManagerController = $app['controllers_factory'];

$messagingManagerController->get('/', function () use ($app) {

    $messaging = new Messaging($app['db']);
    $messages = $messaging->findAll();

    return $app['twig']->render('backend/messagingManager/messagingManager.html.twig', array(
        'messages' => $messages,
    ));
})
->bind('admin_messaging_manager')
;

$messagingManagerController->get('/{id}/delete', function ($id) use ($app) {

    $messaging = new Messaging($app['db']);
    $messaging->delete($id);

    return $app->redirect($app['url_generator']->generate('admin_messaging_manager'));
})
->assert('id', '\d+')
->bind('admin_messaging_manager_delete')
;

$messagingManagerController->assert('_locale', implode('|', $app['languages']));

return $messagingManagerController;
