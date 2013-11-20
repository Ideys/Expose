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

$usersManagerController->assert('_locale', implode('|', $app['languages']));

return $usersManagerController;
