<?php

use Ideys\Content\Item;
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
        $page = new Item\Page(array('type' => Item\Item::ITEM_PAGE));
        $contentFactory->addItem($section, $page);
    }

    $form = $section->addPageForm($app['form.factory'], $page);

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

$htmlManagerController->assert('_locale', implode('|', $app['languages']));

return $htmlManagerController;
