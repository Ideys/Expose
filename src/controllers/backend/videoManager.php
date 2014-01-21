<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$videoManagerController = $app['controllers_factory'];

$videoManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {

    $contentVideo = new ContentVideo($app['db']);
    $section = $contentVideo->findSection($id);

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    return $app['twig']->render('backend/videoManager/_videoSettings.html.twig', array(
        'delete_form' => $deleteForm->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_video_manager_settings')
->method('GET|POST')
;

$videoManagerController->post('/{id}/delete', function (Request $request, $id) use ($app) {

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();
    $contentVideo = new ContentVideo($app['db']);

    $deleteForm->handleRequest($request);
    if ($deleteForm->isValid()) {
        $contentVideo->deleteSection($id);

        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('video.section.deleted'));
    }

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_video_manager_delete')
;

$videoManagerController->assert('_locale', implode('|', $app['languages']));

return $videoManagerController;
