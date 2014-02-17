<?php

use Ideys\Content\ContentFactory;
use Ideys\Content\Item\Video;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$channelManagerController = $app['controllers_factory'];

$channelManagerController->get('/{id}/list', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return $app['twig']->render('backend/channelManager/_videoList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_channel_manager_list')
;

$channelManagerController->match('/{id}/add', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $video = new Video(array('type' => ContentFactory::ITEM_VIDEO));

    $form = $section->addForm($app['form.factory'], $video);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $section->guessVideoCode($video);
        $contentFactory->addItem($section, $video);
        return $app->redirect($app['url_generator']->generate('admin_content_manager'));
    }

    return $app['twig']->render('backend/channelManager/_formAdd.html.twig', array(
        'form' => $form->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_channel_manager_add')
->method('GET|POST')
;

$channelManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $editForm = $section->settingsForm($app['form.factory']);
    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    $editForm->handleRequest($request);
    if ($editForm->isValid()) {
        $contentFactory->updateSection($section);
        return $app->redirect($app['url_generator']->generate('admin_content_manager').'#panel'.$id);
    }

    return $app['twig']->render('backend/channelManager/_channelSettings.html.twig', array(
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_channel_manager_settings')
->method('GET|POST')
;

$channelManagerController->post('/{id}/delete', function (Request $request, $id) use ($app) {

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();
    $contentFactory = new ContentFactory($app);

    $deleteForm->handleRequest($request);
    if ($deleteForm->isValid()) {
        $contentFactory->deleteSection($id);

        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('video.section.deleted'));
    }

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_channel_manager_delete')
;

$channelManagerController->assert('_locale', implode('|', $app['languages']));

return $channelManagerController;
