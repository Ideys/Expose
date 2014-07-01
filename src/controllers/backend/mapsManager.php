<?php

use Ideys\Content\Item\Place;
use Ideys\Content\ContentFactory;
use Symfony\Component\HttpFoundation\Request;

$mapsManagerController = $app['controllers_factory'];

$mapsManagerController->get('/{id}/preview', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return $app['twig']->render('backend/mapsManager/_mapsPreview.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_maps_manager_preview')
;

$mapsManagerController->match('/{id}/integrations', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $sections = $app['db']
        ->fetchAll(
            'SELECT s.id, s.expose_section_id, '.
            's.type, s.slug, s.visibility, '.
            't.title, t.description, t.legend, t.parameters '.
            'FROM expose_section AS s '.
            'LEFT JOIN expose_section_trans AS t '.
            'ON t.expose_section_id = s.id '.
            'WHERE s.type NOT IN  (\'dir\', \'maps\') '.
            'AND s.archive = 0 '.
            'ORDER BY s.hierarchy ',
            array($section->id));

    return $app['twig']->render('backend/mapsManager/_mapsIntegrations.html.twig', array(
        'section' => $section,
        'other_sections' => $sections,
    ));
})
->assert('id', '\d+')
->bind('admin_maps_manager_integrations')
->method('GET|POST')
;

$mapsManagerController->match('/{id}/attach/{sectionId}', function (Request $request, $id, $sectionId) use ($app) {

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

    return $app->json(true);
})
->assert('id', '\d+')
->assert('sectionId', '\d+')
->bind('admin_maps_manager_attach')
->method('POST')
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

    return $app['twig']->render('backend/mapsManager/_mapsPlaces.html.twig', array(
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
