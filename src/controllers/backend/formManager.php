<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$formManagerController = $app['controllers_factory'];

$formManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $contentForm = new ContentForm($app['db']);
    $fields = $contentForm->findSectionItems($id);

    $form = $app['form.factory']->createBuilder('form')
        ->add('type', 'choice', array(
            'choices'       => ContentForm::getFieldTypesChoice(),
            'label'         => 'form.field.type',
        ))
        ->add('title', 'text', array(
            'label'         => 'form.label',
            'attr' => array(
                'placeholder' => 'form.label',
            ),
        ))
        ->add('required', 'checkbox', array(
            'label'         => 'form.required',
            'required' => false,
        ))
        ->add('description', 'textarea', array(
            'label'         => 'form.help',
            'attr' => array(
                'placeholder' => 'form.help',
            ),
            'required' => false,
        ))
        ->add('options', 'textarea', array(
            'label'         => 'form.specs',
            'attr' => array(
                'placeholder' => 'form.specs',
            ),
            'required' => false,
        ))
        ->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $data['path'] = null;
        $data['content'] = $data['options'];
        $data['parameters'] = array(
            'required' => $data['required'],
            'options' => $data['options'],
        );
        $language = 'fr';
        $contentForm->blame($app['security'])->addItem(
                $id,
                $data['type'],
                $data['path'],
                $data['title'],
                $data['description'],
                $data['content'],
                $data['parameters'],
                $language
        );
        return $app->redirect($app['url_generator']->generate('admin_form_manager_edit', array('id' => $id)));
    }

    return $app['twig']->render('backend/formManager/_formEdit.html.twig', array(
        'form' => $form->createView(),
        'fields' => $fields,
        'section_id' => $id,
    ));
})
->assert('id', '\d+')
->bind('admin_form_manager_edit')
->method('GET|POST')
;

$formManagerController->get('/{id}/results', function (Request $request, $id) use ($app) {

    $contentForm = new ContentForm($app['db']);
    $results = $contentForm->getResults($id);
    $fields = $contentForm->findSectionItems($id);

    return $app['twig']->render('backend/formManager/_formResults.html.twig', array(
        'fields' => $fields,
        'results' => $results,
    ));
})
->assert('id', '\d+')
->bind('admin_form_manager_results')
;

$formManagerController->post('/{id}/remove/field', function ($id) use ($app) {

    $contentForm = new ContentForm($app['db']);
    $isDeleted = $contentForm->deleteItem($id);

    $jsonResponse = $isDeleted;

    return $app->json($jsonResponse);
})
->assert('id', '\d+')
->bind('admin_form_manager_remove_field')
;

$formManagerController->post('/{id}/remove/result', function ($id) use ($app) {

    $contentForm = new ContentForm($app['db']);
    $isDeleted = $contentForm->deleteResult($id);

    return $app->json($isDeleted);
})
->assert('id', '\d+')
->bind('admin_form_manager_remove_result')
;

$formManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {
    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    return $app['twig']->render('backend/formManager/_formSettings.html.twig', array(
        'delete_form' => $deleteForm->createView(),
        'section_id' => $id,
    ));
})
->assert('id', '\d+')
->bind('admin_form_manager_settings')
->method('GET|POST')
;

$formManagerController->post('/{id}/delete', function (Request $request, $id) use ($app) {

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();
    $contentForm = new ContentForm($app['db']);

    $deleteForm->handleRequest($request);
    if ($deleteForm->isValid()) {
        $contentForm->deleteSection($id);

        $app['session']
            ->getFlashBag()
            ->add('default', $app['translator']->trans('form.form.deleted'));
    }

    return $app->redirect($app['url_generator']->generate('admin_content_manager'));
})
->assert('id', '\d+')
->bind('admin_form_manager_delete')
;

$formManagerController->assert('_locale', implode('|', $app['languages']));

return $formManagerController;
