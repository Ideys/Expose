<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$frontendController = $app['controllers_factory'];

$frontendController->get('/', function () use ($app) {

    return $app['twig']->render('frontend/homepage.html.twig');
})
->bind('homepage')
;

$frontendController->get('/theme/{slug}', function ($slug) use ($app) {

    $content = new Content($app['db']);
    $section = $content->findSection($slug);
    $items = $content->findSectionItems($section['id']);
    $form = null;

    if (Content::CONTENT_FORM == $section['type']) {
        $dynamicForm = new DynamicForm($app['db'], $app['form.factory']);
        $generatedForm = $dynamicForm->generateFormFields($items);
        $form = $generatedForm->createView();
    }

    return $app['twig']->render('frontend/'.$section['type'].'.html.twig', array(
      'section' => $section,
      'items' => $items,
      'form' => $form,
    ));
})
->bind('section')
;

$frontendController->match('/contact', function (Request $request) use ($app) {

    $messaging = new Messaging($app['db']);

    $form = $app['form.factory']->createBuilder('form')
        ->add('name', 'text', array(
            'constraints'   => array(
                new Assert\Length(array('min' => 3)),
                new Assert\NotBlank(),
            ),
            'label'         => 'contact.name',
        ))
        ->add('email', 'email', array(
            'constraints'   => array(
                new Assert\Email(),
                new Assert\NotBlank(),
            ),
            'label'         => 'contact.email',
        ))
        ->add('message', 'textarea', array(
            'constraints'   => array(
                new Assert\Length(array('min' => 10)),
                new Assert\NotBlank(),
            ),
            'label'         => 'contact.message',
        ))
        ->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $messaging->create(
            $data['name'],
            $data['email'],
            $data['message']
        );
        $app['session']
            ->getFlashBag()
            ->add('success', $app['translator']->trans('contact.info.sent'));
        return $app->redirect($app['url_generator']->generate('contact'));
    }

    return $app['twig']->render('frontend/contact.html.twig', array(
        'form' => $form->createView(),
    ));
})
->bind('contact')
->method('GET|POST')
;

$frontendController->assert('_locale', implode('|', $app['languages']));

return $frontendController;
