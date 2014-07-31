<?php

use Ideys\Picture;
use Ideys\Content\ContentFactory;
use Symfony\Component\HttpFoundation\Request;

$galleryManagerController = $app['controllers_factory'];

$galleryManagerController->get('/{id}/list', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return $app['twig']->render('backend/galleryManager/_slideList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_gallery_manager_list')
;

$galleryManagerController->match('/{id}/labels', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $formBuilder = $app['form.factory']->createBuilder('form')
        ->add('global_legend', 'textarea', array(
            'label'     => 'gallery.global.legend',
            'data'      => $section->legend,
            'required'  => false,
        ));

    foreach ($section->getItems('Slide') as $slide) {
        // Generate related slide fields
        $formBuilder
        ->add('title'.$slide->id, 'text', array(
            'required'      => false,
            'label'         => 'gallery.picture.alt',
            'data'          => $slide->title,
            'attr' => array(
                'placeholder' => 'gallery.picture.alt',
            ),
        ))
        ->add('description'.$slide->id, 'textarea', array(
            'required'      => false,
            'label'         => 'section.description',
            'data'          => $slide->description,
            'attr' => array(
                'placeholder' => 'section.description',
            ),
        ))
        ->add('tags'.$slide->id, 'text', array(
            'required'      => false,
            'label'         => 'tags',
            'data'          => $slide->tags,
            'attr' => array(
                'placeholder' => 'tags',
            ),
        ))
        ->add('link'.$slide->id, 'text', array(
            'required'      => false,
            'label'         => 'gallery.slide.link',
            'data'          => $slide->link,
            'attr' => array(
                'placeholder' => 'gallery.slide.link',
            ),
        ));

        // Retrieve more data about picture
        $slide->setMetaData(Picture::getMetaData(WEB_DIR.'/gallery/'.$slide->path));
    }
    $form = $formBuilder->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();

        // Update the global legend
        $section->legend = $data['global_legend'];
        $contentFactory->updateSection($section);

        // Update each items legends
        foreach ($section->getItems('Slide') as $slide) {
            $contentFactory->updateItemTitle(
                $slide->id,
                $data['title'.$slide->id],
                $data['description'.$slide->id],
                $data['tags'.$slide->id],
                $data['link'.$slide->id]
            );
        }
        return $app->redirect(
            $app['url_generator']->generate(
                'admin_gallery_manager_labels',
                array('id' => $id))
            );
    }

    return $app['twig']->render('backend/galleryManager/_labelsList.html.twig', array(
        'section' => $section,
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
    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($sectionId);
    $jsonResponse = array();

    foreach ($uploadedFiles['files'] as $file) {
        $slide = $section->addSlide($app['imagine'], $file);
        $contentFactory->addItem($section, $slide);

        $jsonResponse[] = array(
            'path' => $slide->path,
            'id' => $slide->id,
        );
    }

    return $app->json($jsonResponse);
})
->bind('admin_gallery_manager_upload')
;

$galleryManagerController->post('/{id}/delete/slides', function (Request $request, $id) use ($app) {

    $itemIds = $request->get('items');
    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $deletedIds = $section->deleteSlides($itemIds);

    return $app->json($deletedIds);
})
->assert('id', '\d+')
->bind('admin_gallery_manager_delete_slides')
;

$galleryManagerController->get('/{id}/pic-manager', function ($id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return $app['twig']->render('backend/galleryManager/_contentSectionsPicManager.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_gallery_manager_content_sections_pic_manager')
;

$galleryManagerController->assert('_locale', implode('|', $app['languages']));

return $galleryManagerController;
