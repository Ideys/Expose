<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$contentManagerController = $app['controllers_factory'];

$contentManagerController->match('/', function (Request $request) use ($app) {

    $contentFactory = new ContentFactory($app);
    $form = $contentFactory->createForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $contentFactory->addSection($data);
        return $app->redirect($app['url_generator']->generate('admin_content_manager'));
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

$contentManagerController->post('/delete/items', function (Request $request) use ($app) {

    $items = $request->get('items');
    $contentFactory = new ContentFactory($app);
    $response = array();

    foreach ($items as $item) {
        $contentFactory->deleteItem($item);
        $response[] = $item;
    }

    return $app->json($response);
})
->bind('admin_content_manager_delete_items')
;

$contentManagerController->get('/{id}/homepage', function ($id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $contentFactory->defindHomepage($id);

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_content_manager_define_homepage')
;

$contentManagerController->assert('_locale', implode('|', $app['languages']));

return $contentManagerController;
