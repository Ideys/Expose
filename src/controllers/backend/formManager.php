<?php

use Ideys\Content\ContentFactory;
use Ideys\Content\Section\Form;
use Ideys\Content\Item\Field;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$formManagerController = $app['controllers_factory'];

$formManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $field = new Field(array('type' => ContentFactory::ITEM_FIELD));

    $form = $app['form.factory']->createBuilder('form', $field)
        ->add('category', 'choice', array(
            'choices' => Field::getTypesChoice(),
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
            'choices' => \Ideys\Settings::getIOChoices(),
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
        return $app->redirect($app['url_generator']->generate('admin_form_manager_edit', array('id' => $id)));
    }

    return $app['twig']->render('backend/formManager/_formEdit.html.twig', array(
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

    return $app['twig']->render('backend/formManager/_formResults.html.twig', array(
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_form_manager_results')
;

$formManagerController->post('/{id}/remove/field/{itemId}', function ($id, $itemId) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $isDeleted = $section->deleteItem($itemId);

    $jsonResponse = $isDeleted;

    return $app->json($jsonResponse);
})
->assert('id', '\d+')
->bind('admin_form_manager_remove_field')
;

$formManagerController->post('/{id}/remove/result/{resultId}', function ($id, $resultId) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);
    $isDeleted = $section->deleteResult($resultId);

    return $app->json($isDeleted);
})
->assert('id', '\d+')
->bind('admin_form_manager_remove_result')
;

$formManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $editForm = $section->settingsForm($app['form.factory']);
    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    $editForm->handleRequest($request);
    if ($editForm->isValid()) {
        $contentFactory->updateSection($section);
        return $app->redirect($app['url_generator']->generate('admin_content_manager'));
    }

    return $app['twig']->render('backend/formManager/_formSettings.html.twig', array(
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_form_manager_settings')
->method('GET|POST')
;

$formManagerController->post('/{id}/delete', function (Request $request, $id) use ($app) {

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();
    $contentFactory = new ContentFactory($app);

    $deleteForm->handleRequest($request);
    if ($deleteForm->isValid()) {
        $contentFactory->deleteSection($id);

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
