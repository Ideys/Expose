<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$frontendController = $app['controllers_factory'];

$frontendContent = function (Request $request, $slug = null) use ($app) {

    $content = new Content($app['db']);

    if (null === $slug) {
        $section = $content->findHomepage($slug);
        $contentType = 'homepage';
    } else {
        $section = $content->findSectionBySlug($slug);
        $contentType = $section['type'];
    }
    $items = $content->findSectionItems($section['id']);
    $formView = null;

    if (Content::CONTENT_FORM == $section['type']) {
        $contentForm = new ContentForm($app['db']);
        $contentForm->setFormFactory($app['form.factory']);
        $form = $contentForm->generateFormFields($items);
        if ($contentForm->checkSubmitedForm($section['id'], $request, $form)) {
            return $app->redirect($app['url_generator']->generate('section', array('slug' => $slug)));
        }
        $formView = $form->createView();
    }

    return $app['twig']->render('frontend/content.html.twig', array(
      'contentType' => $contentType,
      'section' => $section,
      'items' => $items,
      'form' => $formView,
    ));
};

$frontendController->get('/', $frontendContent)
->bind('homepage')
;

$frontendController->match('/theme/{slug}', $frontendContent)
->bind('section')
->method('GET|POST')
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
