<?php

use Ideys\User\UserProvider;
use Ideys\User\Profile;
use Ideys\User\ProfileType;
use Symfony\Component\HttpFoundation\Request;

$userProfileController = $app['controllers_factory'];

$userProfileController->match('/', function (Request $request, $id = null) use ($app) {

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
        return $app->redirect($app['url_generator']->generate('admin_user_profile'));
    }

    return $app['twig']->render('backend/userProfile/userProfile.html.twig', array(
        'form' => $form->createView(),
    ));
})
->value('id', null)
->assert('id', '\d+')
->bind('admin_user_profile')
->method('GET|POST')
;

$userProfileController->assert('_locale', implode('|', $app['languages']));

return $userProfileController;
