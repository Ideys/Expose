<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$contentManagerController = $app['controllers_factory'];

$contentManagerController->match('/', function (Request $request) use ($app) {

    $content = new Content($app);
    $items = $content->findItems();
    $form = $content->createForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $content->blame($app['security'])->addSection($data);
        return $app->redirect($app['url_generator']->generate('admin_content_manager'));
    }

    return $app['twig']->render('backend/contentManager/contentManager.html.twig', array(
        'form' => $form->createView(),
        'items' => $items,
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
    $content = new Content($app);
    $response = array();

    foreach ($items as $item) {
        $content->deleteItem($item);
        $response[] = $item;
    }

    return $app->json($response);
})
->bind('admin_content_manager_delete_items')
;

$contentManagerController->post('/{id}/toggle', function ($id) use ($app) {

    $content = new Content($app);
    $response = $content->toggleSection($id);

    return $app->json($response);
})
->assert('id', '\d+')
->bind('admin_content_manager_toggle_section')
;

$contentManagerController->get('/{id}/homepage', function ($id) use ($app) {

    $content = new Content($app);
    $content->defindHomepage($id);

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_content_manager_define_homepage')
;

$contentManagerController->assert('_locale', implode('|', $app['languages']));

return $contentManagerController;
