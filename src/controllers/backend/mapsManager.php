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

$mapsManagerController->assert('_locale', implode('|', $app['languages']));

return $mapsManagerController;
