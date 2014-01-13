<?php

use Symfony\Component\HttpFoundation\Request;

$galleryManagerController = $app['controllers_factory'];

$galleryManagerController->get('/{id}/list', function (Request $request, $id) use ($app) {

    $contentGallery = new ContentGallery($app['db']);
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

$galleryManagerController->post('/upload', function (Request $request) use ($app) {

    $uploadedFiles = $request->files->all();
    $sectionId = (int) $request->request->get('sectionId');
    if (0 == $sectionId) {
        $sectionId = null;
    }
    $contentGallery = new ContentGallery($app['db']);
    $jsonResponse = array();

    foreach ($uploadedFiles['files'] as $file) {
        $data = array(
            'type' => $file->getMimeType(),
            'title' => $file->getClientOriginalName(),
            'description' => null,
            'content' => null,
            'parameters' => array(),
            'language' => 'fr',
        );
        $fileExt = $file->guessClientExtension();
        $realExt = $file->guessExtension();// from mime type
        $fileSize = $file->getClientSize();
        $data['path'] = uniqid('expose').'.'.$fileExt;
        $data['parameters']['real_ext'] = $realExt;
        $data['parameters']['file_size'] = $fileSize;

        $file->move($app['gallery.dir'], $data['path']);

        $contentGallery->blame($app['security'])->addItem(
                $sectionId,
                $data['type'],
                $data['path'],
                $data['title'],
                $data['description'],
                $data['content'],
                $data['parameters'],
                $data['language']
        );
        $transformation = new \Imagine\Filter\Transformation();
        $transformation->thumbnail(new \Imagine\Image\Box(220, 220))
            ->save($app['gallery.dir'].'/220/'.$data['path']);
        $transformation->apply($app['imagine']
            ->open($app['gallery.dir'].'/'.$data['path']));

        $jsonResponse[] = array(
            'path' => $data['path'],
            'id' => $sectionId,
        );
    }

    return $app->json($jsonResponse);
})
->bind('admin_gallery_manager_upload')
;

$galleryManagerController->post('/{id}/delete/slides', function (Request $request, $id) use ($app) {

    $itemIds = $request->get('items');
    $contentGallery = new ContentGallery($app['db']);

    $deletedIds = $contentGallery->deleteSlides($id, $itemIds);

    return $app->json($deletedIds);
})
->assert('id', '\d+')
->bind('admin_gallery_manager_delete_slides')
;

$galleryManagerController->post('/{id}/delete', function (Request $request, $id) use ($app) {

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();
    $contentGallery = new ContentGallery($app['db']);

    $deleteForm->handleRequest($request);
    if ($deleteForm->isValid()) {
        $contentGallery->deleteSection($id);

        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('picture.gallery.deleted'));
    }

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_gallery_manager_delete')
;

$galleryManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {

    $contentGallery = new ContentGallery($app['db']);
    $section = $contentGallery->findSection($id);

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    return $app['twig']->render('backend/galleryManager/_gallerySettings.html.twig', array(
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
