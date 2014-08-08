<?php

use Ideys\SilexHooks;
use Ideys\Content\ContentFactory;
use Ideys\Content\Section;
use Ideys\Content\Item;
use Symfony\Component\HttpFoundation\Request;

$blogManagerController = SilexHooks::controllerFactory($app);

$blogManagerController->get('/{id}/list', function ($id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return SilexHooks::twig($app)->render('backend/blogManager/_postsList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_blog_manager_list')
;

$blogManagerController->match('/{id}/new', function (Request $request, $id) use ($app) {

    $urlGenerator = SilexHooks::urlGenerator($app);

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $post = new Item\Post(array('type' => Item\Item::ITEM_POST));

    $form = $section->newPostForm($app['form.factory'], $post);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->addItem($section, $post);
        return SilexHooks::redirect($app, 'admin_content_manager', array(), '#panel'.$id);
    }

    return SilexHooks::twig($app)->render('backend/blogManager/_postEdit.html.twig', array(
        'form' => $form->createView(),
        'form_action' => $urlGenerator->generate('admin_blog_manager_new_post', array('id' => $id)),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_blog_manager_new_post')
->method('GET|POST')
;

$blogManagerController->match('/{sectionId}/{id}/edit', function (Request $request, $sectionId, $id) use ($app) {

    $urlGenerator = SilexHooks::urlGenerator($app);

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($sectionId);
    $post = $contentFactory->findItem($id);
    $blog = new Section\Blog($app['db']);

    $form = $blog->newPostForm($app['form.factory'], $post);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->editItem($post);
        return SilexHooks::redirect($app, 'admin_content_manager', array(), '#panel'.$sectionId);
    }

    return SilexHooks::twig($app)->render('backend/blogManager/_postEdit.html.twig', array(
        'form' => $form->createView(),
        'form_action' => $urlGenerator->generate('admin_blog_manager_edit_post', array(
                'sectionId' => $sectionId,
                'id' => $id,
            )),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_blog_manager_edit_post')
->method('GET|POST')
;

$blogManagerController->assert('_locale', implode('|', $app['languages']));

return $blogManagerController;
