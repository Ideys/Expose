<?php

use Ideys\SilexHooks;
use Ideys\Picture;
use Ideys\Content\Item\Entity\Slide;
use Ideys\Content\Item\Provider\SlideProvider;
use Ideys\Content\Section\Provider\GalleryProvider;
use Symfony\Component\HttpFoundation\Request;

$galleryManagerController = SilexHooks::controllerFactory($app);

$galleryManagerController->get('/{id}/list', function ($id) use ($app) {

    $galleryProvider = new GalleryProvider($app['db'], $app['security']);
    $section = $galleryProvider->find($id);

    return SilexHooks::twig($app)->render('backend/galleryManager/_slideList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_gallery_manager_list')
;

$galleryManagerController->match('/{id}/labels', function (Request $request, $id) use ($app) {

    $galleryProvider = new GalleryProvider($app['db'], $app['security']);
    $section = $galleryProvider->find($id);

    $formBuilder = SilexHooks::formFactory($app)->createBuilder('form')
        ->add('global_legend', 'textarea', array(
            'label'     => 'gallery.global.legend',
            'data'      => $section->getLegend(),
            'required'  => false,
        ));

    foreach ($section->getItems('Slide') as $slide) {
        if ($slide instanceof Slide)
        // Generate related slide fields
        $formBuilder
        ->add('title'.$slide->getId(), 'text', array(
            'required'      => false,
            'label'         => 'gallery.picture.alt',
            'data'          => $slide->getTitle(),
            'attr' => array(
                'placeholder' => 'gallery.picture.alt',
            ),
        ))
        ->add('description'.$slide->getId(), 'textarea', array(
            'required'      => false,
            'label'         => 'section.description',
            'data'          => $slide->getDescription(),
            'attr' => array(
                'placeholder' => 'section.description',
            ),
        ))
        ->add('tags'.$slide->getId(), 'text', array(
            'required'      => false,
            'label'         => 'tags',
            'data'          => $slide->getTags(),
            'attr' => array(
                'placeholder' => 'tags',
            ),
        ))
        ->add('link'.$slide->getId(), 'text', array(
            'required'      => false,
            'label'         => 'gallery.slide.link',
            'data'          => $slide->getLink(),
            'attr' => array(
                'placeholder' => 'gallery.slide.link',
            ),
        ));

        // Retrieve more data about picture
        $slide->setMetaData(Picture::getMetaData(WEB_DIR.'/gallery/'.$slide->getPath()));
    }
    $form = $formBuilder->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();

        // Update the global legend
        $section->setLegend($data['global_legend']);
        $galleryProvider->updateSection($section);

        // Update each items legends
        foreach ($section->getItems('Slide') as $slide) {
            if ($slide instanceof Slide)
            $galleryProvider->updateItemTitle(
                $slide->getId(),
                $data['title'.$slide->getId()],
                $data['description'.$slide->getId()],
                $data['tags'.$slide->getId()],
                $data['link'.$slide->getId()]
            );
        }
        return SilexHooks::redirect($app, 'admin_gallery_manager_labels', array('id' => $id));
    }

    return SilexHooks::twig($app)->render('backend/galleryManager/_labelsList.html.twig', array(
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
    $galleryProvider = new GalleryProvider($app['db'], $app['security']);
    $slideProvider = new SlideProvider($app['db'], $app['security']);
    $section = $galleryProvider->find($sectionId);
    $jsonResponse = array();

    foreach ($uploadedFiles['files'] as $file) {
        $slide = $section->addSlide($app['imagine'], $file);
        $galleryProvider->addItem($section, $slide);

        $jsonResponse[] = array(
            'path' => $slide->getPath(),
            'id' => $slide->getId(),
        );
    }

    return $app->json($jsonResponse);
})
->bind('admin_gallery_manager_upload')
;

$galleryManagerController->post('/{id}/delete/slides', function (Request $request, $id) use ($app) {

    $itemIds = $request->get('items');
    $galleryProvider = new GalleryProvider($app['db'], $app['security']);
    $section = $galleryProvider->find($id);

    $deletedIds = $section->deleteSlides($itemIds);

    return $app->json($deletedIds);
})
->assert('id', '\d+')
->bind('admin_gallery_manager_delete_slides')
;

$galleryManagerController->get('/{id}/pic-manager', function ($id) use ($app) {

    $galleryProvider = new GalleryProvider($app['db'], $app['security']);
    $section = $galleryProvider->find($id);

    return SilexHooks::twig($app)->render('backend/galleryManager/_contentSectionsPicManager.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_gallery_manager_content_sections_pic_manager')
;

$galleryManagerController->assert('_locale', implode('|', $app['languages']));

return $galleryManagerController;
