<?php

use Ideys\SilexHooks;
use Ideys\Content\Item\Entity\Field;
use Ideys\Content\Item\Type\ItemTypeFactory;
use Ideys\Content\Section\Provider\FormProvider;
use Ideys\Files\File;
use Symfony\Component\HttpFoundation\Request;

$formManagerController = SilexHooks::controllerFactory($app);

$formManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $formProvider = new FormProvider($app['db'], $app['security']);
    $section = $formProvider->find($id);

    $itemTypeFactory = new ItemTypeFactory($app['form.factory']);
    $field = new Field();
    $form = $itemTypeFactory->createForm($field);

    $form->handleRequest($request);

    if ($form->isValid()) {
        $contentFactory->addItem($section, $field);
        return SilexHooks::redirect($app, 'admin_form_manager_edit', array('id' => $id));
    }

    return SilexHooks::twig($app)->render('backend/formManager/_formEdit.html.twig', array(
        'form' => $form->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_form_manager_edit')
->method('GET|POST')
;

$formManagerController->get('/{id}/results', function ($id) use ($app) {

    $formProvider = new FormProvider($app['db'], $app['security']);
    $section = $formProvider->find($id);
    $results = $formProvider->getResults($section);

    return SilexHooks::twig($app)->render('backend/formManager/_formResults.html.twig', array(
        'section' => $section,
        'results' => $results,
    ));
})
->assert('id', '\d+')
->bind('admin_form_manager_results')
;

$formManagerController->get('/download/{file}', function ($file) use ($app) {

    $filePath = File::getDir().'/'.$file;

    if (!file_exists($filePath)) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $mode = \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT;

    return $app->sendFile($filePath)->setContentDisposition($mode, $file);
})
->assert('file', '[\w-\.]+')
->bind('admin_form_manager_download_file')
;

$formManagerController->post('/{id}/remove/field/{itemId}', function ($id, $itemId) use ($app) {

    $formProvider = new FormProvider($app['db'], $app['security']);
    $section = $formProvider->find($id);
    $isDeleted = $section->deleteItem($itemId);

    $jsonResponse = $isDeleted;

    return $app->json($jsonResponse);
})
->assert('id', '\d+')
->assert('itemId', '\d+')
->bind('admin_form_manager_remove_field')
;

$formManagerController->post('/{id}/remove/result/{resultId}', function ($id, $resultId) use ($app) {

    $formProvider = new FormProvider($app['db'], $app['security']);
    $section = $formProvider->find($id);
    $isDeleted = $section->deleteResult($resultId);

    return $app->json($isDeleted);
})
->assert('id', '\d+')
->assert('resultId', '\d+')
->bind('admin_form_manager_remove_result')
;

$formManagerController->assert('_locale', implode('|', $app['languages']));

return $formManagerController;
