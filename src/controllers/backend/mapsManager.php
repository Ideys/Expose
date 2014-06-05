<?php

use Ideys\Content\Item\Place;
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

$mapsManagerController->match('/{id}/places', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $place = new Place(array('type' => ContentFactory::ITEM_PLACE));
    $form = $section->addPlaceForm($app['form.factory'], $place);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->addItem($section, $place);
    }

    return $app['twig']->render('backend/mapsManager/_mapPlaces.html.twig', array(
        'form' => $form->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_maps_manager_places')
->method('GET|POST')
;

$mapsManagerController->assert('_locale', implode('|', $app['languages']));

return $mapsManagerController;
