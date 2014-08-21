<?php

use Ideys\SilexHooks;
use Ideys\Content\Item\Entity\Page;
use Ideys\Content\Item\Type\ItemTypeFactory;
use Ideys\Content\Section\Provider\HtmlProvider;
use Symfony\Component\HttpFoundation\Request;

$htmlManagerController = SilexHooks::controllerFactory($app);

$htmlManagerController->get('/{id}/preview', function ($id) use ($app) {

    $sectionProvider = new HtmlProvider($app['db']);
    $section = $sectionProvider->find($id);

    return SilexHooks::twig($app)->render('backend/htmlManager/_pagePreview.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_html_manager_preview')
;

$htmlManagerController->get('/{id}/display-preview', function ($id) use ($app) {

    $sectionProvider = new HtmlProvider($app['db']);
    $section = $sectionProvider->find($id);

    return SilexHooks::twig($app)->render('frontend/html/html.html.twig', array(
      'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_html_manager_display_preview')
;

$htmlManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $sectionProvider = new HtmlProvider($app['db']);
    $section = $sectionProvider->find($id);

    $page = $section->getFirstPage();
    if (empty($page)) {
        $page = new Page();
        $contentFactory->addItem($section, $page);
    }

    $itemTypeFactory = new ItemTypeFactory($app['form.factory']);
    $form = $itemTypeFactory->createForm($page);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->editItem($page);
    }

    return SilexHooks::twig($app)->render('backend/htmlManager/_pageEdit.html.twig', array(
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
