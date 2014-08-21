<?php

use Ideys\SilexHooks;
use Ideys\Content\Item\Entity\Place;
use Ideys\Content\Section\Provider\MapProvider;
use Ideys\Content\Item\Type\PlaceType;
use Ideys\Content\Item\Type\CoordinatesType;
use Symfony\Component\HttpFoundation\Request;

$mapManagerController = SilexHooks::controllerFactory($app);

$mapManagerController->get('/{id}/preview', function ($id) use ($app) {

    $mapProvider = new MapProvider($app['db']);
    $section = $mapProvider->find($id);

    return SilexHooks::twig($app)->render('backend/mapManager/_mapPreview.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_map_manager_preview')
;

$mapManagerController->match('/{id}/places', function (Request $request, $id) use ($app) {

    $mapProvider = new MapProvider($app['db']);
    $section = $mapProvider->find($id);

    $linkableSections = $mapProvider->findLinkableSections();

    $place = new Place();
    $placeType = new PlaceType($app['form.factory']);
    $form = $placeType->formBuilder($place)->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $mapProvider->addItem($section, $place);
    }

    return SilexHooks::twig($app)->render('backend/mapManager/_mapPlaces.html.twig', array(
        'form' => $form->createView(),
        'section' => $section,
        'linkable_sections' => $linkableSections,
    ));
})
->assert('id', '\d+')
->bind('admin_map_manager_places')
->method('GET|POST')
;

$mapManagerController->post('/{id}/attach/{sectionId}', function ($id, $sectionId) use ($app) {

    $mapProvider = new MapProvider($app['db']);
    $section = $mapProvider->find($id);

    $section->toggleConnectedSectionId($sectionId);

    $app['db']->update(
        'expose_section',
        array(
            'connected_sections' => implode(',', $section->getConnectedSectionsId()),
        ),
        array('id' => $id)
    );

    return SilexHooks::twig($app)->render('backend/mapManager/_placesList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->assert('sectionId', '\d+')
->bind('admin_map_manager_attach')
;

$mapManagerController->match('/{id}/coordinates', function (Request $request, $id) use ($app) {

    $mapProvider = new MapProvider($app['db']);
    $item = $mapProvider->find($id);

    $coordinatesType = new CoordinatesType($app['form.factory']);
    $form = $coordinatesType->formBuilder($item)->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $contentFactory->editItem($item);
        return $app->json(true);
    }

    return SilexHooks::twig($app)->render('backend/mapManager/_coordinatesForm.html.twig', array(
        'item' => $item,
        'form' => $form->createView(),
    ));
})
->assert('id', '\d+')
->bind('admin_map_manager_coordinates')
->method('GET|POST')
;

$mapManagerController->assert('_locale', implode('|', $app['languages']));

return $mapManagerController;
