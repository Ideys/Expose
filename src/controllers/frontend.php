<?php

use Ideys\SilexHooks;
use Ideys\Content\ContentFactory;
use Ideys\User\UserProvider;
use Ideys\User\ProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception;

$frontendController = SilexHooks::controllerFactory($app);

$frontendContent = function (Request $request, $slug = null, $itemSlug = null) use ($app) {

    $contentFactory = new ContentFactory($app);
    $settings = new \Ideys\Settings\Settings($app['db']);

    if (null === $slug) {
        $section = $contentFactory->findHomepage($slug);
    } else {
        $section = $contentFactory->findSectionBySlug($slug);
    }

    if (!$section) {
        throw new Exception\NotFoundHttpException();
    }

    $security = SilexHooks::security($app);
    $session = SilexHooks::session($app);
    $translator = SilexHooks::translator($app);
    $urlGenerator = SilexHooks::urlGenerator($app);

    if (!$section->isHomepage() && $settings->maintenance
            && (false === $security->isGranted('ROLE_ADMIN')) ) {
        $session
            ->getFlashBag()
            ->add('warning', $translator->trans('site.maintenance.message'));
        return $app->redirect($urlGenerator->generate('homepage'));
    }

    if ($section->isPrivate() && (false === $security->isGranted('ROLE_USER'))
     || $section->isClosed()) {
        $session
            ->getFlashBag()
            ->add('warning', $translator->trans('section.unavailable'));
        return $app->redirect($urlGenerator->generate('homepage'));
    }

    // Multiple page sections logic
    $item = null;
    if (null !== $itemSlug) {
        $item = $section->getItemFromSlug($itemSlug);

        if (!$item) {
            throw new Exception\NotFoundHttpException();
        }

    } elseif ($section->hasMultiplePages()) {

        $items = $section->getItems();
        $firstItem = array_shift($items);
        $itemSlug = $firstItem->slug;

        return $app->redirect($urlGenerator->generate('section_item', array(
            'slug' => $slug,
            'itemSlug' => $itemSlug,
        )));
    }

    // Handle composite sections with other sections inclusions
    $contentFactory->composeSectionItems($section, $app['twig']);

    // Form sections logic
    $formView = null;
    if ($section instanceof \Ideys\Content\Section\Form) {
        $form = $section->generateFormFields($app['form.factory']);
        if ($section->checkSubmittedForm($request, $form)) {
            $validationMessage = $section->getParameter('validation_message');
            $session
                ->getFlashBag()
                ->add('success', empty($validationMessage) ?
                        $translator->trans('form.validation.message.default'):
                    $validationMessage);
            return $app->redirect($urlGenerator->generate('section', array('slug' => $slug)));
        }
        $formView = $form->createView();
    }

    return SilexHooks::twig($app)->render('frontend/'.$section->getType().'/'.$section->getType().'.html.twig', array(
      'section' => $section,
      'item' => $item,
      'form' => $formView,
    ));
};

$frontendController->get('/', $frontendContent)
->bind('homepage')
;

$frontendController->get('/first', function() use ($app) {
    $contentFactory = new ContentFactory($app);

    $firstSection = $contentFactory->findFirstSection();

    return SilexHooks::redirect($app, 'section', array('slug' => $firstSection->getSlug()));
})
->bind('first_section')
;

$frontendController->match('/s/{slug}', $frontendContent)
->bind('section')
->method('GET|POST')
;

$frontendController->match('/s/{slug}/{itemSlug}', $frontendContent)
->bind('section_item')
->method('GET|POST')
;

$frontendController->match('/contact', function (Request $request) use ($app) {

    $settings = new \Ideys\Settings\Settings($app['db']);
    $messaging = new \Ideys\Messaging\MessageProvider($app['db']);
    $message = new \Ideys\Messaging\Message();
    $messageType = new \Ideys\Messaging\MessageType($app['form.factory']);

    if ('disabled' === $settings->contactSection) {
        throw new Exception\NotFoundHttpException();
    }

    $translator = SilexHooks::translator($app);
    $form = $messageType->form($message);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $messaging->persist($message);
        $messaging->sendByEmail($settings, $translator, $message);
        SilexHooks::session($app)
            ->getFlashBag()
            ->add('success', $translator->trans('contact.info.sent'));
        return SilexHooks::redirect($app, 'contact');
    }

    return SilexHooks::twig($app)->render('frontend/contact.html.twig', array(
        'form' => $form->createView(),
    ));
})
->bind('contact')
->method('GET|POST')
;

$frontendController->match('/profile', function (Request $request, $id = null) use ($app) {

    $translator = SilexHooks::translator($app);
    $session = SilexHooks::session($app);

    $userProvider = new UserProvider($app['db'], $app['session']);

    $profile = $session->get('profile');

    $profileType = new ProfileType($app['form.factory']);
    $form = $profileType->form($profile);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $userProvider->persist($app['security.encoder_factory'], $profile);
        $session
            ->getFlashBag()
            ->add('default', $translator->trans('user.updated'));
        return SilexHooks::redirect($app, 'user_profile');
    }

    return SilexHooks::twig($app)->render('frontend/userProfile.html.twig', array(
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
    $filesHandler = new \Ideys\Files\FilesHandler($app['db']);
    $file = $filesHandler->findBySlugAndToken($slug, $token);

    if ('0' === $settings->shareFiles) {
        throw new Exception\AccessDeniedHttpException();
    }

    if (!file_exists($file->getPath())) {
        throw new Exception\NotFoundHttpException();
    }

    $filesHandler->logDownload($file->getRecipients()[0]);

    if (null !== $app['request']->query->get('preview')) {
        $mode = \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_INLINE;
    } else {
        $mode = \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT;
    }

    return $app->sendFile($file->getPath())
               ->setContentDisposition($mode, $file->getSlug().'.'.$file->getFileExt());
})
->bind('file_share')
->assert('token', '\w+')
->assert('slug', '[\w-\.]+')
;

$frontendController->assert('_locale', implode('|', $app['languages']));

return $frontendController;
