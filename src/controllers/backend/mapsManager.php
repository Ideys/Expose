<?php

use Ideys\Content\Section\Maps;
use Ideys\Content\Item\Place;
use Ideys\Content\ContentFactory;
use Symfony\Component\HttpFoundation\Request;

$mapsManagerController = $app['controllers_factory'];

$mapsManagerController->get('/{id}/preview', function ($id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return $app['twig']->render('backend/mapsManager/_mapsPreview.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_maps_manager_preview')
;

$mapsManagerController->match('/{id}/places', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $linkableSections = $section->getLinkableSections();

    $place = new Place(array('type' => ContentFactory::ITEM_PLACE));
    $form = $section->addPlaceForm($app['form.factory'], $place);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->addItem($section, $place);
    }

    return $app['twig']->render('backend/mapsManager/_mapsPlaces.html.twig', array(
        'form' => $form->createView(),
        'section' => $section,
        'linkable_sections' => $linkableSections,
    ));
})
->assert('id', '\d+')
->bind('admin_maps_manager_places')
->method('GET|POST')
;

$mapsManagerController->post('/{id}/attach/{sectionId}', function ($id, $sectionId) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $section->toggleConnectedSectionId($sectionId);

    $app['db']->update(
        'expose_section',
        array(
            'connected_sections' => implode(',', $section->getConnectedSectionsId()),
        ),
        array('id' => $id)
    );

    return $app['twig']->render('backend/mapsManager/_placesList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->assert('sectionId', '\d+')
->bind('admin_maps_manager_attach')
;

$mapsManagerController->match('/{id}/coordinates', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $item = $contentFactory->findItem($id);

    $maps = new Maps($app['db']);
    $form = $maps->coordinatesForm($app['form.factory'], $item);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->editItem($item);
        return $app->json(true);
    }

    return $app['twig']->render('backend/mapsManager/_coordinatesForm.html.twig', array(
        'item' => $item,
        'form' => $form->createView(),
    ));
})
->assert('id', '\d+')
->bind('admin_maps_manager_coordinates')
->method('GET|POST')
;

$mapsManagerController->assert('_locale', implode('|', $app['languages']));

return $mapsManagerController;
