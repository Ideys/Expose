<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$contentManagerController = $app['controllers_factory'];

$contentManagerController->match('/', function (Request $request) use ($app) {

    $content = new Content($app['db']);
    $dirsChoice = array();
    $sections = $content->findSections();
    foreach ($sections as $section) {
        if ('dir' === $section['type']) {
            $dirsChoice[$section['id']] = $section['title'];
        }
    }

    $form = $app['form.factory']->createBuilder('form')
        ->add('type', 'choice', array(
            'choices'       => array(
                'slideshow' => 'content.slideshow',
                'video' => 'content.video',
                'page' => 'content.page',
                'dir' => 'content.dir',
            ),
            'label'         => 'content.type',
        ))
        ->add('title', 'text', array(
            'label'         => 'section.title',
        ))
        ->add('description', 'textarea', array(
            'required'      => false,
            'label'         => 'section.description',
        ))
        ->add('dir', 'choice', array(
            'choices'       => $dirsChoice,
            'required'      => false,
            'label'         => 'content.dir',
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
                $data['dir'],
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
