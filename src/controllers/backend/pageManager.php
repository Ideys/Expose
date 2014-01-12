<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$pageManagerController = $app['controllers_factory'];

$pageManagerController->get('/{id}/preview', function (Request $request, $id) use ($app) {

    $contentPage = new ContentPage($app['db']);
    $pages = $contentPage->findSectionItems($id);

    return $app['twig']->render('backend/pageManager/_pagePreview.html.twig', array(
        'pages' => $pages,
        'section_id' => $id,
    ));
})
->assert('id', '\d+')
->bind('admin_page_manager_preview')
;

$pageManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $contentPage = new ContentPage($app['db']);
    $pages = $contentPage->findSectionItems($id);
    $page = array_shift($pages);

    $form = $app['form.factory']->createBuilder('form', $page)
        ->add('title', 'text', array(
            'label'         => 'section.title',
            'attr' => array(
                'placeholder' => 'content.title',
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
        $data = $form->getData();
        $contentPage->blame($app['security'])->editItem($data);
    }

    return $app['twig']->render('backend/pageManager/_pageEdit.html.twig', array(
        'form' => $form->createView(),
        'section_id' => $id,
    ));
})
->assert('id', '\d+')
->bind('admin_page_manager_edit')
->method('GET|POST')
;

$pageManagerController->assert('_locale', implode('|', $app['languages']));

return $pageManagerController;
