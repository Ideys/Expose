<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$usersManagerController = $app['controllers_factory'];

$usersManagerController->match('/', function (Request $request) use ($app) {

    $userProvider = new UserProvider($app['db']);

    $form = $app['form.factory']->createBuilder('form')
        ->add('username', 'text', array(
            'constraints'   => array(
                new Assert\Length(array('min' => 5)),
                new Assert\NotBlank(),
            ),
            'label'         => 'user.name',
        ))
        ->add('password', 'password', array(
            'constraints'   => array(
                new Assert\NotBlank()
            ),
            'label'         => 'user.password',
        ))
        ->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $userProvider->addUser(
            $app['security.encoder_factory'],
            $data['username'],
            $data['password']
        );
    }

    $users = $userProvider->findAll();

    return $app['twig']->render('backend/usersManager.html.twig', array(
        'users' => $users,
        'form'  => $form->createView(),
    ));
})
->bind('admin_users_manager')
->method('GET|POST')
;

$usersManagerController->get('/{id}/delete', function ($id) use ($app) {

    $userProvider = new UserProvider($app['db']);
    if (false === $userProvider->deleteUser($id, $app['security'])) {
        $app['session']
            ->getFlashBag()
            ->add('alert', $app['translator']->trans('user.deletion.error'));
    }

    return $app->redirect($app['url_generator']->generate('admin_users_manager'));
})
->bind('admin_user_manager_delete')
;

$usersManagerController->assert('_locale', implode('|', $app['languages']));

return $usersManagerController;
