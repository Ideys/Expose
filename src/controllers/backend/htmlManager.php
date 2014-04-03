<?php

use Ideys\Content\Item\Page;
use Ideys\Content\ContentFactory;
use Symfony\Component\HttpFoundation\Request;

$htmlManagerController = $app['controllers_factory'];

$htmlManagerController->get('/{id}/preview', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return $app['twig']->render('backend/htmlManager/_pagePreview.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_html_manager_preview')
;

$htmlManagerController->get('/{id}/display-preview', function ($id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return $app['twig']->render('frontend/html/html.html.twig', array(
      'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_html_manager_display_preview')
;

$htmlManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $page = $section->getFirstPage();
    if (empty($page)) {
        $page = new Page(array('type' => ContentFactory::ITEM_PAGE));
        $contentFactory->addItem($section, $page);
    }

    $form = $app['form.factory']->createBuilder('form', $page)
        ->add('title', 'text', array(
            'label'         => 'section.title',
            'attr' => array(
                'placeholder' => 'section.title',
            ),
        ))
        ->add('content', 'textarea', array(
            'label'         => 'section.description',
            'attr' => array(
                'placeholder' => 'section.description',
            ),
        ))
        ->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->editItem($page);
    }

    return $app['twig']->render('backend/htmlManager/_pageEdit.html.twig', array(
        'form' => $form->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_html_manager_edit')
->method('GET|POST')
;

$htmlManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $editForm = $section->settingsForm($app['form.factory']);
    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    $editForm->handleRequest($request);
    if ($editForm->isValid()) {
        $contentFactory->updateSection($section);
        return $app->redirect($app['url_generator']->generate('admin_content_manager').'#panel'.$id);
    }

    return $app['twig']->render('backend/htmlManager/_htmlSettings.html.twig', array(
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_html_manager_settings')
->method('GET|POST')
;

$htmlManagerController->assert('_locale', implode('|', $app['languages']));

return $htmlManagerController;
