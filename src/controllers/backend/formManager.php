<?php

use Ideys\SilexHooks;
use Ideys\Content\Item;
use Ideys\Content\ContentFactory;
use Ideys\Files\File;
use Ideys\Settings\Settings;
use Symfony\Component\HttpFoundation\Request;

$formManagerController = SilexHooks::controllerFactory($app);

$formManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $field = new Item\Field(array('type' => Item\Item::ITEM_FIELD));

    $form = $app['form.factory']->createBuilder('form', $field)
        ->add('category', 'choice', array(
            'choices' => Item\Field::getTypesChoice(),
            'label' => 'form.field.type',
        ))
        ->add('title', 'text', array(
            'label' => 'form.label',
            'attr' => array(
                'placeholder' => 'form.label',
            ),
        ))
        ->add('required', 'choice', array(
            'label' => 'form.required',
            'choices' => Settings::getIOChoices(),
        ))
        ->add('description', 'textarea', array(
            'label' => 'form.help',
            'attr' => array(
                'placeholder' => 'form.help',
            ),
            'required' => false,
        ))
        ->add('choices', 'textarea', array(
            'label' => 'form.choices',
            'attr' => array(
                'placeholder' => 'form.choices',
            ),
            'required' => false,
        ))
        ->getForm();

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

$formManagerController->get('/{id}/results', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    return SilexHooks::twig($app)->render('backend/formManager/_formResults.html.twig', array(
        'section' => $section,
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

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $isDeleted = $section->deleteItem($itemId);

    $jsonResponse = $isDeleted;

    return $app->json($jsonResponse);
})
->assert('id', '\d+')
->assert('itemId', '\d+')
->bind('admin_form_manager_remove_field')
;

$formManagerController->post('/{id}/remove/result/{resultId}', function ($id, $resultId) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $isDeleted = $section->deleteResult($resultId);

    return $app->json($isDeleted);
})
->assert('id', '\d+')
->assert('resultId', '\d+')
->bind('admin_form_manager_remove_result')
;

$formManagerController->assert('_locale', implode('|', $app['languages']));

return $formManagerController;
