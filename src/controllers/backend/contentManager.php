<?php

use Ideys\Content\SectionType;
use Ideys\Content\DirType;
use Ideys\Content\ContentFactory;
use Ideys\Settings\Settings;
use Symfony\Component\HttpFoundation\Request;

$contentManagerController = $app['controllers_factory'];

$contentManagerController->match('/', function (Request $request) use ($app) {

    $contentFactory = new ContentFactory($app);
    $sectionType = new SectionType($app['db'], $app['form.factory']);
    $settings = new Settings($app['db']);

    $newSection = new Ideys\Content\Section\Gallery($app['db']);
    $newSection->visibility = $settings->newSectiondefaultVisibility;
    $form = $sectionType->createForm($newSection);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->addSection($newSection);
        return $app->redirect($app['url_generator']->generate('admin_content_manager').'#panel'.$newSection->id);
    }

    return $app['twig']->render('backend/contentManager/contentManager.html.twig', array(
        'form' => $form->createView(),
    ));
})
->bind('admin_content_manager')
->method('GET|POST')
;

$contentManagerController->post('/sort/sections', function (Request $request) use ($app) {

    $hierarchy = $request->get('hierarchy');

    foreach ($hierarchy as $key => $value) {
        $app['db']->update('expose_section',
                array('hierarchy' => $key),
                array('id' => filter_var($value, FILTER_SANITIZE_NUMBER_INT))
        );
    }
    $response = array(true);

    return $app->json($response);
})
->bind('admin_content_manager_sort_sections')
;

$contentManagerController->post('/sort/items', function (Request $request) use ($app) {

    $hierarchy = $request->get('hierarchy');
//    $app['monolog']->addDebug(var_export($hierarchy, true));

    foreach ($hierarchy as $key => $value) {
        $app['db']->update('expose_section_item',
                array('hierarchy' => $key),
                array('id' => filter_var($value, FILTER_SANITIZE_NUMBER_INT))
        );
    }
    $response = array(true);

    return $app->json($response);
})
->bind('admin_content_manager_sort_items')
;

$contentManagerController->post('/move/items/{id}', function (Request $request, $id) use ($app) {

    $itemIds = $request->get('items');
    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $response = array();
    foreach ($itemIds as $id) {
        if ($section->attachItem($id)) {
            $response[] = $id;
        }
    }

    return $app->json($response);
})
->bind('admin_content_manager_move_items')
;

$contentManagerController->get('/{id}/homepage', function ($id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $contentFactory->defindHomepage($id);

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_content_manager_define_homepage')
;

$contentManagerController->get('/{id}/archive', function ($id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $contentFactory->switchArchive($id);

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_content_manager_archive')
;

$contentManagerController->match('/{id}/edit/dir', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $dirType = new DirType($app['form.factory']);
    $form = $dirType->editForm($section);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->updateSection($section);
        return $app->redirect($app['url_generator']->generate('admin_content_manager'));
    }

    return $app['twig']->render('backend/dirManager/_dirForm.html.twig', array(
        'section' => $section,
        'form' => $form->createView(),
    ));
})
->assert('id', '\d+')
->method('GET|POST')
->bind('admin_content_manager_edit_dir')
;

$contentManagerController->assert('_locale', implode('|', $app['languages']));

return $contentManagerController;
