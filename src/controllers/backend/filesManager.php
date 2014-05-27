<?php

use Ideys\Files\FilesHandler;
use Ideys\Files\FileType;
use Ideys\Files\File;
use Symfony\Component\HttpFoundation\Request;

$filesManagerController = $app['controllers_factory'];

$filesManagerController->match('/', function (Request $request) use ($app) {

    $filesHandler = new FilesHandler($app['db']);
    $files = $filesHandler->findAll();
    $file = new File();

    $formFactory = new FileType($app['form.factory']);
    $form = $formFactory->form($file);
    $form->handleRequest($request);
    if ($form->isValid()) {
        $filesHandler->addFile($file);
        return $app->redirect($app['url_generator']->generate('admin_files_manager'));
    }

    return $app['twig']->render('backend/filesManager/filesList.html.twig', array(
        'files' => $files,
        'form' => $form->createView(),
    ));
})
->assert('id', '\d+')
->bind('admin_files_manager')
->method('GET|POST')
;

$filesManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $filesHandler = new FilesHandler($app['db']);
    $file = $filesHandler->find($id);

    $formFactory = new FileType($app['form.factory']);
    $form = $formFactory->editForm($file);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $filesHandler->editTitle($file);
        return $app->redirect($app['url_generator']->generate('admin_files_manager'));
    }

    return $app['twig']->render('backend/filesManager/_fileEdit.html.twig', array(
        'file' => $file,
        'form' => $form->createView(),
    ));
})
->assert('id', '\d+')
->method('GET|POST')
->bind('admin_files_manager_edit')
;

$filesManagerController->get('/{id}/delete', function ($id) use ($app) {

    $filesHandler = new FilesHandler($app['db']);

    if (false === $filesHandler->delete($id)) {
        $app['session']
            ->getFlashBag()
            ->add('alert', $app['translator']->trans('file.deletion.error'));
    } else {
        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('file.deleted'));
    }

    return $app->redirect($app['url_generator']->generate('admin_files_manager'));
})
->assert('id', '\d+')
->bind('admin_files_manager_delete')
;

$filesManagerController->assert('_locale', implode('|', $app['languages']));

return $filesManagerController;
