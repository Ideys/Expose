<?php

use Ideys\SilexHooks;
use Ideys\Settings;
use Ideys\Messaging;
use Ideys\Files;
use Ideys\Content\Section\Provider\SectionProvider;
use Ideys\Content\Section\Provider\FormProvider;
use Ideys\Content\Section\Entity\SectionInterface;
use Ideys\Content\Section\Entity\Form;
use Ideys\User\UserProvider;
use Ideys\User\ProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception;

$frontendController = SilexHooks::controllerFactory($app);

$frontendContent = function (Request $request, $slug = null, $itemSlug = null) use ($app) {

    $sectionProvider = new SectionProvider($app);

    $settingsProvider = new Settings\SettingsProvider($app['db']);
    $settings = $settingsProvider->getSettings();

    if (null === $slug) {
        $section = $sectionProvider->findHomepage($app);
    } else {
        $section = $sectionProvider->findBySlug($slug);
    }

    if (!$section) {
        throw new Exception\NotFoundHttpException();
    }

    $security = SilexHooks::security($app);
    $urlGenerator = SilexHooks::urlGenerator($app);

    if (!$section->isHomepage() && $settings->getMaintenance()
            && (false === $security->isGranted('ROLE_ADMIN')) ) {
        SilexHooks::flashMessage($app, 'site.maintenance.message', SilexHooks::FLASH_WARNING);
        return $app->redirect($urlGenerator->generate('homepage'));
    }

    if ($section->isPrivate() && (false === $security->isGranted('ROLE_USER'))
     || $section->isClosed()) {
        SilexHooks::flashMessage($app, 'section.unavailable', SilexHooks::FLASH_WARNING);
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
//    $contentFactory->composeSectionItems($section, $app['twig']);

    // Form sections logic
    $formView = null;
    if ($section instanceof Form) {
        $formProvider = new FormProvider($app);
        $form = $formProvider->generateFormFields($app['form.factory'], $section);
        if ($formProvider->checkSubmittedForm($section, $request, $form)) {
            $validationMessage = $section->getValidationMessage();
            $flashMessage = empty($validationMessage) ? 'form.validation.message.default': $validationMessage;
            SilexHooks::flashMessage($app, $flashMessage, SilexHooks::FLASH_SUCCESS);

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

    $sectionProvider = new SectionProvider($app);

    $firstSection = $sectionProvider->findFirstSection();

    if ($firstSection instanceof SectionInterface) {
        return SilexHooks::redirect($app, 'section', array('slug' => $firstSection->getSlug()));
    } else {
        return SilexHooks::redirect($app, 'homepage');
    }
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

    $settingsProvider = new Settings\SettingsProvider($app['db']);
    $settings = $settingsProvider->getSettings();

    $messageProvider = new Messaging\MessageProvider($app['db']);
    $message = new Messaging\Message();
    $messageType = new Messaging\MessageType($app['form.factory']);

    if (Settings\Settings::CONTACT_SECTION_DISABLED === $settings->getContactSection()) {
        throw new Exception\NotFoundHttpException();
    }

    $translator = SilexHooks::translator($app);
    $form = $messageType->form($message);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $messageProvider->persist($message);
        $messageProvider->sendByEmail($settings, $translator, $message);
        SilexHooks::flashMessage($app, 'contact.info.sent', SilexHooks::FLASH_SUCCESS);
        return SilexHooks::redirect($app, 'contact');
    }

    return SilexHooks::twig($app)->render('frontend/contact.html.twig', array(
        'form' => $form->createView(),
    ));
})
->bind('contact')
->method('GET|POST')
;

$frontendController->match('/profile', function (Request $request) use ($app) {

    $session = SilexHooks::session($app);

    $userProvider = new UserProvider($app['db'], $app['session']);

    $profile = $session->get('profile');

    $profileType = new ProfileType($app['form.factory']);
    $form = $profileType->form($profile);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $userProvider->persist($app['security.encoder_factory'], $profile);
        SilexHooks::flashMessage($app, 'user.updated');
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

    $settingsProvider = new Settings\SettingsProvider($app['db']);
    $settings = $settingsProvider->getSettings();
    $filesHandler = new Files\FileProvider($app['db']);
    $file = $filesHandler->findBySlugAndToken($slug, $token);

    if ('0' === $settings->getShareFiles()) {
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
