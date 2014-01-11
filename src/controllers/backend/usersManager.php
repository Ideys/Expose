<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$usersManagerController = $app['controllers_factory'];

$usersManagerController->match('/{id}', function (Request $request, $id = null) use ($app) {

    $userProvider = new UserProvider($app['db']);

    if ($id > 0) {
        $user = $userProvider->find($id);
    } else {
        $user = array('roles' => UserProvider::ROLE_USER);
    }

    $form = $app['form.factory']->createBuilder('form', $user)
        ->add('username', 'text', array(
            'constraints'   => array(
                new Assert\Length(array('min' => 5)),
                new Assert\NotBlank(),
            ),
            'label'         => 'user.name',
        ))
        ->add('password', 'password', array(
            'constraints'   => ($id > 0) ? array() : array(
                new Assert\NotBlank()
            ),
            'required'      => false,
            'label'         => 'user.password',
        ))
        ->add('roles', 'choice', array(
            'choices' => UserProvider::getRolesChoice(),
            'constraints'   => array(
                new Assert\Choice(UserProvider::getRoles())
            ),
            'label'         => 'user.role.role',
        ))
        ->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $userProvider->persistUser(
            $app['security.encoder_factory'],
            $data['username'],
            $data['password'],
            array($data['roles']),
            $id
        );
        return $app->redirect($app['url_generator']->generate('admin_users_manager'));
    }

    $users = $userProvider->findAll();

    return $app['twig']->render('backend/usersManager/usersManager.html.twig', array(
        'users' => $users,
        'form'  => $form->createView(),
    ));
})
->value('id', null)
->assert('id', '\d+')
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
