<?php

use Ideys\SilexHooks;
use Ideys\Content\Item\Entity\Place;
use Ideys\Content\Section\Provider\MapProvider;
use Ideys\Content\Item\Type\PlaceType;
use Ideys\Content\Item\Type\CoordinatesType;
use Ideys\Content\Item\Provider\PlaceProvider;
use Symfony\Component\HttpFoundation\Request;

$mapManagerController = SilexHooks::controllerFactory($app);

$mapManagerController->get('/{id}/preview', function ($id) use ($app) {

    $mapProvider = new MapProvider($app['db'], $app['security']);
    $section = $mapProvider->find($id);

    return SilexHooks::twig($app)->render('backend/mapManager/_mapPreview.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_map_manager_preview')
;

$mapManagerController->match('/{id}/places', function (Request $request, $id) use ($app) {

    $mapProvider = new MapProvider($app['db'], $app['security']);
    $section = $mapProvider->find($id);

    $linkableSections = $mapProvider->findLinkableSections();

    $place = new Place();
    $placeType = new PlaceType($app['form.factory']);
    $form = $placeType->formBuilder($place)->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $placeProvider = new PlaceProvider($app['db'], $app['security']);
        $placeProvider->create($section, $place);
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

    $mapProvider = new MapProvider($app['db'], $app['security']);
    $section = $mapProvider->find($id);

    $section->toggleConnectedSectionId($sectionId);

    $mapProvider->persist($section);

    return SilexHooks::twig($app)->render('backend/mapManager/_placesList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->assert('sectionId', '\d+')
->bind('admin_map_manager_attach')
;

$mapManagerController->match('/{id}/coordinates', function (Request $request, $id) use ($app) {

    $placeProvider = new PlaceProvider($app['db'], $app['security']);
    $item = $placeProvider->find($id);

    $coordinatesType = new CoordinatesType($app['form.factory']);
    $form = $coordinatesType->formBuilder($item)->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $placeProvider->update($item);
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
