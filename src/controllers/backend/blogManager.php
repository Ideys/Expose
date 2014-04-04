<?php

use Ideys\Content\ContentFactory;
use Ideys\Content\Item\Post;
use Symfony\Component\HttpFoundation\Request;

$blogManagerController = $app['controllers_factory'];

$blogManagerController->get('/{id}/list', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return $app['twig']->render('backend/blogManager/_postsList.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_blog_manager_list')
;

$blogManagerController->match('/{id}/new', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $post = new Post(array('type' => ContentFactory::ITEM_POST));

    $form = $section->newPostForm($app['form.factory'], $post);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->addItem($section, $post);
        return $app->redirect($app['url_generator']->generate('admin_content_manager').'#panel'.$id);
    }

    return $app['twig']->render('backend/blogManager/_formNew.html.twig', array(
        'form' => $form->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_blog_manager_new_post')
->method('GET|POST')
;

$blogManagerController->assert('_locale', implode('|', $app['languages']));

return $blogManagerController;
