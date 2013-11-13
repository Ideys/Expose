<?php

$messagingManagerController = $app['controllers_factory'];

$messagingManagerController->get('/', function () use ($app) {

    $messaging = new Messaging($app['db']);
    $messages = $messaging->findAll();

    return $app['twig']->render('backend/messagingManager.html.twig', array(
        'messages' => $messages,
    ));
})
->bind('admin_messaging_manager')
;

$messagingManagerController->assert('_locale', implode('|', $app['languages']));

return $messagingManagerController;
