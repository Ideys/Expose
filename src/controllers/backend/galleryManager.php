<?php

use Symfony\Component\HttpFoundation\Request;

$galleryManagerController = $app['controllers_factory'];

$galleryManagerController->get('/{id}/list', function (Request $request, $id) use ($app) {

    $contentGallery = new ContentGallery($app);
    $slides = $contentGallery->findSectionItems($id);
    $sections = $contentGallery->findSections();

    return $app['twig']->render('backend/galleryManager/_slideList.html.twig', array(
        'section_id' => $id,
        'sections' => $sections,
        'slides' => $slides,
    ));
})
->assert('id', '\d+')
->bind('admin_gallery_manager_list')
;

$galleryManagerController->match('/{id}/labels', function (Request $request, $id) use ($app) {

    $contentGallery = new ContentGallery($app);
    $slides = $contentGallery->findSectionItems($id);
    $formBuilder = $app['form.factory']->createBuilder('form', $slides);
    foreach ($slides as $slide) {
    $formBuilder
        ->add('title'.$slide['id'], 'text', array(
            'required'      => false,
            'label'         => 'section.title',
            'data'          => $slide['title'],
            'attr' => array(
                'placeholder' => 'section.title',
            ),
        ))
        ->add('description'.$slide['id'], 'textarea', array(
            'required'      => false,
            'label'         => 'section.description',
            'data'          => $slide['description'],
            'attr' => array(
                'placeholder' => 'section.description',
            ),
        ));
    }
    $form = $formBuilder->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        foreach ($slides as $slide) {
            $contentGallery->updateItemTitle(
                $slide['id'],
                $data['title'.$slide['id']],
                $data['description'.$slide['id']]
            );
        }
        return $app->redirect(
            $app['url_generator']->generate(
                'admin_gallery_manager_labels',
                array('id' => $id))
            );
    }

    return $app['twig']->render('backend/galleryManager/_labelsList.html.twig', array(
        'section_id' => $id,
        'slides' => $slides,
        'form' => $form->createView(),
    ));
})
->assert('id', '\d+')
->bind('admin_gallery_manager_labels')
->method('GET|POST')
;

$galleryManagerController->post('/upload', function (Request $request) use ($app) {

    $uploadedFiles = $request->files->all();
    $sectionId = (int) $request->request->get('sectionId');
    if (0 == $sectionId) {
        $sectionId = null;
    }
    $contentGallery = new ContentGallery($app);
    $jsonResponse = array();

    foreach ($uploadedFiles['files'] as $file) {
        $item = array(
            'expose_section_id' => $sectionId,
            'type' => $file->getMimeType(),
            'title' => null,
            'description' => null,
            'content' => null,
            'parameters' => array(),
        );
        $fileExt = $file->guessClientExtension();
        $realExt = $file->guessExtension();// from mime type
        $fileSize = $file->getClientSize();
        $item['path'] = uniqid('expose').'.'.$fileExt;
        $item['parameters']['real_ext'] = $realExt;
        $item['parameters']['file_size'] = $fileSize;
        $item['parameters']['original_name'] = $file->getClientOriginalName();

        $file->move($app['gallery.dir'], $item['path']);

        $contentGallery->blame($app['security'])->addItem($item);
        $transformation = new \Imagine\Filter\Transformation();
        $transformation->thumbnail(new \Imagine\Image\Box(220, 220))
            ->save($app['gallery.dir'].'/220/'.$item['path']);
        $transformation->apply($app['imagine']
            ->open($app['gallery.dir'].'/'.$item['path']));

        $jsonResponse[] = array(
            'path' => $item['path'],
            'id' => $sectionId,
        );
    }

    return $app->json($jsonResponse);
})
->bind('admin_gallery_manager_upload')
;

$galleryManagerController->post('/{id}/delete/slides', function (Request $request, $id) use ($app) {

    $itemIds = $request->get('items');
    $contentGallery = new ContentGallery($app);

    $deletedIds = $contentGallery->deleteSlides($id, $itemIds);

    return $app->json($deletedIds);
})
->assert('id', '\d+')
->bind('admin_gallery_manager_delete_slides')
;

$galleryManagerController->post('/{id}/delete', function (Request $request, $id) use ($app) {

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();
    $contentGallery = new ContentGallery($app);

    $deleteForm->handleRequest($request);
    if ($deleteForm->isValid()) {
        $contentGallery->deleteSection($id);

        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('gallery.deleted'));
    }

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_gallery_manager_delete')
;

$galleryManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {

    $contentGallery = new ContentGallery($app);
    $section = $contentGallery->findSection($id);

    $editForm = $contentGallery->editForm($section);
    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    $editForm->handleRequest($request);
    if ($editForm->isValid()) {
        $section = $editForm->getData();
        $contentGallery->blame($app['security'])->updateSection($section);
        return $app->redirect($app['url_generator']->generate('admin_content_manager'));
    }

    return $app['twig']->render('backend/galleryManager/_gallerySettings.html.twig', array(
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_gallery_manager_settings')
->method('GET|POST')
;

$galleryManagerController->assert('_locale', implode('|', $app['languages']));

return $galleryManagerController;
