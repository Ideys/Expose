<?php

use Ideys\SilexHooks;
use Ideys\Content\ContentFactory;
use Ideys\Content\Item\Entity\Video;
use Symfony\Component\HttpFoundation\Request;

$channelManagerController = SilexHooks::controllerFactory($app);

$channelManagerController->get('/{id}/list', function ($id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return SilexHooks::twig($app)->render('backend/channelManager/_videoList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_channel_manager_list')
;

$channelManagerController->match('/{id}/add', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $video = new Video();

    $form = $section->addForm($app['form.factory'], $video);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $section->guessVideoCode($video);
        $contentFactory->addItem($section, $video);
        return SilexHooks::redirect($app, 'admin_content_manager', array(), '#panel'.$id);
    }

    return SilexHooks::twig($app)->render('backend/channelManager/_formAdd.html.twig', array(
        'form' => $form->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_channel_manager_add')
->method('GET|POST')
;

$channelManagerController->get('/{id}/remove/video/{itemId}', function ($id, $itemId) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $isDeleted = $section->deleteItem($itemId);

    return SilexHooks::redirect($app, 'admin_content_manager', array(), '#panel'.$id);
})
->assert('id', '\d+')
->assert('itemId', '\d+')
->bind('admin_channel_manager_remove_video')
;

$channelManagerController->assert('_locale', implode('|', $app['languages']));

return $channelManagerController;
