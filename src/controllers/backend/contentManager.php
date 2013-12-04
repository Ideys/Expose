<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$contentManagerController = $app['controllers_factory'];

$contentManagerController->match('/', function (Request $request) use ($app) {

    $content = new Content($app['db']);
    $dirsChoice = array();
    $sections = $content->findSections();
    $items = $content->findItems();

    foreach ($sections as $section) {
        if ('dir' === $section['type']) {
            $dirsChoice[$section['id']] = $section['title'];
        }
    }

    $form = $app['form.factory']->createBuilder('form', array('active' => true))
        ->add('type', 'choice', array(
            'choices'       => Content::getContentTypesChoice(),
            'label'         => 'content.type',
        ))
        ->add('title', 'text', array(
            'label'         => 'section.title',
            'attr' => array(
                'placeholder' => 'section.new',
            ),
        ))
        ->add('description', 'textarea', array(
            'required'      => false,
            'label'         => 'section.description',
            'attr' => array(
                'placeholder' => 'section.description',
            ),
        ))
        ->add('dir', 'choice', array(
            'choices'       => $dirsChoice,
            'required'      => false,
            'label'         => 'content.dir',
            'empty_value'   => 'content.root',
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
        'items' => $items,
    ));
})
->bind('admin_content_manager')
->method('GET|POST')
;

$contentManagerController->assert('_locale', implode('|', $app['languages']));

return $contentManagerController;
