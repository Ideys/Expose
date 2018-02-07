<?php

use Ideys\SilexHooks;
use Ideys\Files;
use Symfony\Component\HttpFoundation\Request;

$filesManagerController = SilexHooks::controllerFactory($app);

$filesManagerController->match('/', function (Request $request) use ($app) {

    $filesHandler = new Files\FileProvider($app['db']);
    $files = $filesHandler->findAll();
    $file = new Files\File();

    $formFactory = new Files\FileType($app['form.factory']);
    $form = $formFactory->form($file);
    $form->handleRequest($request);
    if ($form->isValid()) {
        $filesHandler->addFile($file);
        return SilexHooks::redirect($app, 'admin_files_manager');
    }

    return SilexHooks::twig($app)->render('backend/filesManager/filesList.html.twig', array(
        'files' => $files,
        'form' => $form->createView(),
    ));
})
->assert('id', '\d+')
->bind('admin_files_manager')
->method('GET|POST')
;

$filesManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $filesHandler = new Files\FileProvider($app['db']);
    $file = $filesHandler->find($id);

    $formFactory = new Files\FileType($app['form.factory']);
    $form = $formFactory->editForm($file);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $filesHandler->editTitle($file);
        return SilexHooks::redirect($app, 'admin_files_manager');
    }

    return SilexHooks::twig($app)->render('backend/filesManager/_fileEdit.html.twig', array(
        'file' => $file,
        'form' => $form->createView(),
    ));
})
->assert('id', '\d+')
->method('GET|POST')
->bind('admin_files_manager_edit')
;

$filesManagerController->match('/{fileId}/edit/recipient/{id}', function (Request $request, $fileId, $id) use ($app) {

    $filesHandler = new Files\FileProvider($app['db']);
    $file = $filesHandler->find($fileId);
    $recipient = $file->getRecipient($id);
    if (null === $recipient) {
        $recipient = new Files\Recipient();
        $recipient->setFile($file);
    }

    $recipientType = new Files\RecipientType($app['form.factory']);
    $form = $recipientType->form($recipient);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $filesHandler->persistRecipient($recipient);
        return SilexHooks::redirect($app, 'admin_files_manager');
    }

    return SilexHooks::twig($app)->render('backend/filesManager/_recipientForm.html.twig', array(
        'file' => $file,
        'recipient' => $recipient,
        'form' => $form->createView(),
    ));
})
->assert('fileId', '\d+')
->assert('id', '\d+')
->method('GET|POST')
->bind('admin_files_manager_edit_recipient')
;

$filesManagerController->get('/{id}/delete', function ($id) use ($app) {

    $filesHandler = new Files\FileProvider($app['db']);

    if (false === $filesHandler->delete($id)) {
        SilexHooks::flashMessage($app, 'file.deletion.error', SilexHooks::FLASH_ALERT);
    } else {
        SilexHooks::flashMessage($app, 'file.deleted');
    }

    return SilexHooks::redirect($app, 'admin_files_manager');
})
->assert('id', '\d+')
->bind('admin_files_manager_delete')
;

$filesManagerController->assert('_locale', implode('|', $app['languages']));

return $filesManagerController;
