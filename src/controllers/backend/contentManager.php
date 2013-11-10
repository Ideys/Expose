<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$contentManagerController = $app['controllers_factory'];

$contentManagerController->match('/', function (Request $request) use ($app) {

    $content = new Content($app['db']);

    $form = $app['form.factory']->createBuilder('form')
        ->add('type', 'choice', array(
            'choices'       => array(
                'slideshow' => 'section.slideshow',
                'video' => 'section.video',
                'page' => 'section.page',
            ),
            'label'         => 'section.type',
        ))
        ->add('title', 'text', array(
            'label'         => 'section.title',
        ))
        ->add('description', 'textarea', array(
            'required'      => false,
            'label'         => 'section.description',
        ))
        ->add('active', 'checkbox', array(
            'required'      => false,
            'label'         => 'section.active',
        ))
        ->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $language = 'fr';
        $content->addSection(
                $data['type'],
                $data['title'],
                $data['description'],
                $language,
                $data['active']
        );
        return $app->redirect($app['url_generator']->generate('admin_content_manager'));
    }

    return $app['twig']->render('backend/contentManager.html.twig', array(
        'form' => $form->createView(),
    ));
})
->bind('admin_content_manager')
->method('GET|POST')
;

$contentManagerController->assert('_locale', implode('|', $app['languages']));

return $contentManagerController;
