<?php

use Ideys\Content\ContentFactory;
use Ideys\User\UserProvider;
use Ideys\User\ProfileType;
use Symfony\Component\HttpFoundation\Request;

$frontendController = $app['controllers_factory'];

$frontendContent = function (Request $request, $slug = null, $itemSlug = null) use ($app) {

    $contentFactory = new ContentFactory($app);

    if (null === $slug) {
        $section = $contentFactory->findHomepage($slug);
    } else {
        $section = $contentFactory->findSectionBySlug($slug);
    }

    if (!$section) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    if ($section->isPrivate() && (false === $app['security']->isGranted('ROLE_USER'))
     || $section->isClosed()) {
        $app['session']
            ->getFlashBag()
            ->add('warning', $app['translator']->trans('section.unavailable'));
        return $app->redirect($app['url_generator']->generate('homepage'));
    }

    // Multiple page sections logic
    $item = null;
    if (null !== $itemSlug) {
        $item = $section->getItemFromSlug($itemSlug);

        if (!$item) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

    } elseif ($section->hasMultiplePages()) {

        $items = $section->getItems();
        $firstItem = array_shift($items);
        $itemSlug = $firstItem->slug;

        return $app->redirect($app['url_generator']->generate('section_item', array(
            'slug' => $slug,
            'itemSlug' => $itemSlug,
        )));
    }

    // Form sections logic
    $formView = null;
    if ($section instanceof Ideys\Content\Section\Form) {
        $form = $section->generateFormFields($app['form.factory']);
        if ($section->checkSubmitedForm($request, $form)) {
            $validationMessage = $section->getParameter('validation_message');
            $app['session']
                ->getFlashBag()
                ->add('success', empty($validationMessage) ?
                        $app['translator']->trans('form.validation.message.default'):
                    $validationMessage);
            return $app->redirect($app['url_generator']->generate('section', array('slug' => $slug)));
        }
        $formView = $form->createView();
    }

    return $app['twig']->render('frontend/'.$section->type.'/'.$section->type.'.html.twig', array(
      'section' => $section,
      'item' => $item,
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

$frontendController->match('/theme/{slug}/{itemSlug}', $frontendContent)
->bind('section_item')
->method('GET|POST')
;

$frontendController->match('/contact', function (Request $request) use ($app) {

    $settings = new \Ideys\Settings\Settings($app['db']);
    $messaging = new \Ideys\Messaging\Messaging($app['db']);
    $message = new \Ideys\Messaging\Message();
    $messageType = new \Ideys\Messaging\MessageType($app['form.factory']);

    if ('disabled' === $settings->contactSection) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $form = $messageType->form($message);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $messaging->persist($message);
        $messaging->sendByEmail($settings, $app['translator'], $message);
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

$frontendController->match('/profile', function (Request $request, $id = null) use ($app) {

    $userProvider = new UserProvider($app['db'], $app['session']);

    $profile = $app['session']->get('profile');

    $profileType = new ProfileType($app['form.factory']);
    $form = $profileType->form($profile);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $userProvider->persist($app['security.encoder_factory'], $profile);
        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('user.updated'));
        return $app->redirect($app['url_generator']->generate('user_profile'));
    }

    return $app['twig']->render('frontend/userProfile.html.twig', array(
        'form' => $form->createView(),
    ));
})
->value('id', null)
->assert('id', '\d+')
->bind('user_profile')
->method('GET|POST')
->secure('ROLE_USER')
;

$frontendController->get('/files/{token}/{slug}', function ($token, $slug) use ($app) {

    $settings = new \Ideys\Settings\Settings($app['db']);
    $filesHandeler = new \Ideys\Files\FilesHandeler($app['db']);
    $file = $filesHandeler->findBySlugAndToken($slug, $token);

    if ('0' === $settings->shareFiles) {
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
    }

    if (!file_exists($file->getPath())) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $forceDownload = (null !== $app['request']->query->get('d'));

    if ($forceDownload) {
        $mode = \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT;
    } else {
        $mode = \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_INLINE;
    }

    return $app->sendFile($file->getPath())
               ->setContentDisposition($mode, $file->getName());
});

$frontendController->assert('_locale', implode('|', $app['languages']));

return $frontendController;
