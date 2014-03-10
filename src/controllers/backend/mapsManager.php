<?php

use Ideys\Content\Item\Map;
use Ideys\Content\ContentFactory;
use Symfony\Component\HttpFoundation\Request;

$mapsManagerController = $app['controllers_factory'];

$mapsManagerController->get('/{id}/preview', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return $app['twig']->render('backend/mapsManager/_mapPreview.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_maps_manager_preview')
;

$mapsManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $editForm = $section->settingsForm($app['form.factory']);
    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    $editForm->handleRequest($request);
    if ($editForm->isValid()) {
        $contentFactory->updateSection($section);
        return $app->redirect($app['url_generator']->generate('admin_content_manager').'#panel'.$id);
    }

    return $app['twig']->render('backend/mapsManager/_mapsSettings.html.twig', array(
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_maps_manager_settings')
->method('GET|POST')
;

$mapsManagerController->assert('_locale', implode('|', $app['languages']));

return $mapsManagerController;
