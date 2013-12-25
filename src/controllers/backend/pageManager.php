<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$pageManagerController = $app['controllers_factory'];

$pageManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $content = new Content($app['db']);
    $pages = $content->findSectionItems($id);
    $page = $pages[0];

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
        $content->blame($app['security'])->editItem($data);
    }

    return $app['twig']->render('backend/_pageEdit.html.twig', array(
        'form' => $form->createView(),
        'section_id' => $id,
    ));
})
->bind('admin_page_manager_edit')
->method('GET|POST')
;

$pageManagerController->assert('_locale', implode('|', $app['languages']));

return $pageManagerController;
