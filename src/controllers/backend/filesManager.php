<?php

use Ideys\Files\FilesHandeler;
use Ideys\Files\FileType;
use Ideys\Files\File;
use Symfony\Component\HttpFoundation\Request;

$filesManagerController = $app['controllers_factory'];

$filesManagerController->match('/', function (Request $request) use ($app) {

    $filesHandeler = new FilesHandeler($app['db']);
    $files = $filesHandeler->findAll();
    $file = new File();

    $formFactory = new FileType($app['form.factory']);
    $form = $formFactory->form($file);
    $form->handleRequest($request);
    if ($form->isValid()) {
        $filesHandeler->addFile($file);
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
