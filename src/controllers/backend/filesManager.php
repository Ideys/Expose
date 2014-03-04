<?php

use Ideys\Files\FilesHandeler;
use Symfony\Component\HttpFoundation\Request;

$filesManagerController = $app['controllers_factory'];

$filesManagerController->get('/', function (Request $request) use ($app) {

    $filesHandeler = new FilesHandeler($app['db']);
    $files = $filesHandeler->findAll();

    return $app['twig']->render('backend/filesManager/filesList.html.twig', array(
        'files' => $files,
    ));
})
->assert('id', '\d+')
->bind('admin_files_manager')
;

$filesManagerController->assert('_locale', implode('|', $app['languages']));

return $filesManagerController;
