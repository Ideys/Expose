<?php

use Ideys\User\UserProvider;
use Ideys\User\Profile;
use Ideys\User\ProfileType;
use Symfony\Component\HttpFoundation\Request;

$usersManagerController = $app['controllers_factory'];

$usersManagerController->match('/{id}', function (Request $request, $id = null) use ($app) {

    $userProvider = new UserProvider($app['db'], $app['session']);

    if ($id > 0) {
        $profile = $userProvider->find($id);
    } else {
        $profile = new Profile();
    }

    $profileType = new ProfileType($app['form.factory'], true);
    $form = $profileType->form($profile);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $userProvider->persist($app['security.encoder_factory'], $profile);
        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('user.updated'));
        return $app->redirect($app['url_generator']->generate('admin_users_manager'));
    }

    $users = $userProvider->findAll();

    return $app['twig']->render('backend/usersManager/usersManager.html.twig', array(
        'users' => $users,
        'edited_profile' => $profile,
        'form'  => $form->createView(),
    ));
})
->value('id', null)
->assert('id', '\d+')
->bind('admin_users_manager')
->method('GET|POST')
;

$usersManagerController->get('/{id}/delete', function ($id) use ($app) {

    $userProvider = new UserProvider($app['db'], $app['session']);

    if (false === $userProvider->deleteUser($id, $app['security'])) {
        $app['session']
            ->getFlashBag()
            ->add('alert', $app['translator']->trans('user.deletion.error'));
    } else {
        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('user.deleted'));
    }

    return $app->redirect($app['url_generator']->generate('admin_users_manager'));
})
->assert('id', '\d+')
->bind('admin_user_manager_delete')
;

$usersManagerController
->assert('_locale', implode('|', $app['languages']))
->secure('ROLE_SUPER_ADMIN')
;

return $usersManagerController;
