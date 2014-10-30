<?php

use Ideys\SilexHooks;
use Ideys\Content\Section\Provider\BlogProvider;
use Ideys\Content\Item\Entity\Post;
use Ideys\Content\Item\Type\PostType;
use Ideys\Content\Item\Provider\PostProvider;
use Symfony\Component\HttpFoundation\Request;

$blogManagerController = SilexHooks::controllerFactory($app);

$blogManagerController->get('/{id}/list', function ($id) use ($app) {

    $blogProvider = new BlogProvider($app);
    $section = $blogProvider->find($id);

    return SilexHooks::twig($app)->render('backend/blogManager/_postsList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_blog_manager_list')
;

$blogManagerController->match('/{id}/new', function (Request $request, $id) use ($app) {

    $urlGenerator = SilexHooks::urlGenerator($app);

    $blogProvider = new BlogProvider($app);
    $section = $blogProvider->find($id);
    $post = new Post();

    $postType = new PostType($app['form.factory']);
    $form = $postType->formBuilder($post)->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $postProvider = new PostProvider($app);
        $postProvider->create($section, $post);

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

    $blogProvider = new BlogProvider($app);
    $section = $blogProvider->find($id);

    $postProvider = new PostProvider($app);
    $post = $postProvider->find($id);

    if (! $post instanceof Post) {
        throw new \Exception('The item is not a blog post.');
    }

    $postType = new PostType($app['form.factory']);
    $form = $postType->formBuilder($post)->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $postProvider->update($post);

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
