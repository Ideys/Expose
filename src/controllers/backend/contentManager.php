<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$contentManagerController = $app['controllers_factory'];

$contentManagerController->match('/', function (Request $request) use ($app) {

    $content = new Content($app['db']);
    $dirsChoice = array();
    $sections = $content->findSections();
    $items = $content->findItems();

    foreach ($sections as $section) {
        if ('dir' === $section['type']) {
            $dirsChoice[$section['id']] = $section['title'];
        }
    }

    $form = $app['form.factory']->createBuilder('form', array('active' => true))
        ->add('type', 'choice', array(
            'choices'       => Content::getContentTypesChoice(),
            'label'         => 'content.type',
        ))
        ->add('title', 'text', array(
            'label'         => 'section.title',
            'attr' => array(
                'placeholder' => 'section.title',
            ),
        ))
        ->add('description', 'textarea', array(
            'required'      => false,
            'label'         => 'section.description',
            'attr' => array(
                'placeholder' => 'section.description',
            ),
        ))
        ->add('dir', 'choice', array(
            'choices'       => $dirsChoice,
            'required'      => false,
            'label'         => 'content.dir',
            'empty_value'   => 'content.root',
        ))
        ->add('active', 'checkbox', array(
            'required'      => false,
            'label'         => 'section.active',
        ))
        ->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $language = 'fr';
        $content->blame($app['security'])->addSection(
                $data['type'],
                $data['title'],
                $data['description'],
                $data['dir'],
                $language,
                $data['active']
        );
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
    $content = new Content($app['db']);
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

    $content = new Content($app['db']);
    $response = $content->toggleSection($id);

    return $app->json($response);
})
->assert('id', '\d+')
->bind('admin_content_manager_toggle_section')
;

$contentManagerController->assert('_locale', implode('|', $app['languages']));

return $contentManagerController;
