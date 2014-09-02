<?php

use Ideys\SilexHooks;
use Ideys\Content\Item\Entity\Page;
use Ideys\Content\Item\Type\ItemTypeFactory;
use Ideys\Content\Item\Provider\PageProvider;
use Ideys\Content\Section\Entity\Html;
use Ideys\Content\Section\Provider\HtmlProvider;
use Symfony\Component\HttpFoundation\Request;

$htmlManagerController = SilexHooks::controllerFactory($app);

$htmlManagerController->get('/{id}/preview', function ($id) use ($app) {

    $sectionProvider = new HtmlProvider($app['db'], $app['security']);
    $section = $sectionProvider->find($id);

    return SilexHooks::twig($app)->render('backend/htmlManager/_pagePreview.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_html_manager_preview')
;

$htmlManagerController->get('/{id}/display-preview', function ($id) use ($app) {

    $sectionProvider = new HtmlProvider($app['db'], $app['security']);
    $section = $sectionProvider->find($id);

    return SilexHooks::twig($app)->render('frontend/html/html.html.twig', array(
      'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_html_manager_display_preview')
;

$htmlManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $sectionProvider = new HtmlProvider($app['db'], $app['security']);
    $pageProvider = new PageProvider($app['db'], $app['security']);
    $section = $sectionProvider->find($id);

    if (! $section instanceof Html) {
        throw new \Exception('The section is not an HTML content.');
    }

    $page = $section->getFirstPage();
    if (empty($page)) {
        $page = new Page();
        $pageProvider->create($section, $page);
    }

    $itemTypeFactory = new ItemTypeFactory($app['form.factory']);
    $form = $itemTypeFactory->createForm($page);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $pageProvider->update($page);
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
