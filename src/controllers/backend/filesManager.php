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

$filesManagerController->assert('_locale', implode('|', $app['languages']));

return $filesManagerController;
