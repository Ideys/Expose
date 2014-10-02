<?php

use Ideys\SilexHooks;
use Ideys\User\UserProvider;
use Ideys\User\ProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception;

$profileController = SilexHooks::controllerFactory($app);

$profileController->get('/', function () use ($app) {

    $session = SilexHooks::session($app);

    $profile = $session->get('profile');

    return SilexHooks::twig($app)->render('frontend/profile/profile.html.twig', array(
        'profile' => $profile,
    ));
})
->bind('user_profile')
->secure('ROLE_USER')
;

$profileController->match('/edit', function (Request $request) use ($app) {

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

    return SilexHooks::twig($app)->render('frontend/profile/profileForm.html.twig', array(
        'form' => $form->createView(),
    ));
})
->bind('user_profile_edit')
->method('GET|POST')
->secure('ROLE_USER')
;

$profileController->assert('_locale', implode('|', $app['languages']));

return $profileController;
