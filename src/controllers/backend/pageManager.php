<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$pageManagerController = $app['controllers_factory'];

$pageManagerController->get('/{id}/preview', function (Request $request, $id) use ($app) {

    $contentPage = new ContentPage($app);
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

    $contentPage = new ContentPage($app);
    $pages = $contentPage->findSectionItems($id);
    $page = array_shift($pages);
    if (empty($page)) {
        $page = $contentPage->addFirstPage($id);
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

$pageManagerController->post('/{id}/delete', function (Request $request, $id) use ($app) {

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();
    $contentPage = new ContentPage($app);

    $deleteForm->handleRequest($request);
    if ($deleteForm->isValid()) {
        $contentPage->deleteSection($id);

        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('page.section.deleted'));
    }

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_page_manager_delete')
;

$pageManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {

    $contentPage = new ContentPage($app);
    $section = $contentPage->findSection($id);

    $editForm = $contentPage->editForm($section);
    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    $editForm->handleRequest($request);
    if ($editForm->isValid()) {
        $section = $editForm->getData();
        $contentPage->blame($app['security'])->updateSection($section);
        return $app->redirect($app['url_generator']->generate('admin_content_manager'));
    }

    return $app['twig']->render('backend/pageManager/_pageSettings.html.twig', array(
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_page_manager_settings')
->method('GET|POST')
;

$pageManagerController->assert('_locale', implode('|', $app['languages']));

return $pageManagerController;
