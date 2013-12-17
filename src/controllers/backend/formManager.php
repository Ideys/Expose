<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$formManagerController = $app['controllers_factory'];

$formManagerController->match('/{id}/edit', function (Request $request, $id) use ($app) {

    $content = new Content($app['db']);
    $dynamicForm = new DynamicForm($app['db'], $app['form.factory']);
    $fields = $content->findSectionItems($id);

    $form = $app['form.factory']->createBuilder('form')
        ->add('type', 'choice', array(
            'choices'       => DynamicForm::getFieldTypesChoice(),
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
        $data['content'] = serialize(array(
            'required' => $data['required'],
            'options' => $data['options'],
        ));
        $language = 'fr';
        $content->blame($app['security'])->addItem(
                $id,
                $data['type'],
                $data['path'],
                $data['title'],
                $data['description'],
                $data['content'],
                $language
        );
        // Call fields with new result
        $fields = $content->findSectionItems($id);
    }

    return $app['twig']->render('backend/_formEdit.html.twig', array(
        'form' => $form->createView(),
        'fields' => $fields,
        'section_id' => $id,
    ));
})
->bind('admin_form_manager_edit')
->method('GET|POST')
;

$formManagerController->get('/{id}/results', function (Request $request, $id) use ($app) {

    $dynamicForm = new DynamicForm($app['db'], $app['form.factory']);
    $results = $dynamicForm->getResults($id);

    return $app['twig']->render('backend/_formResults.html.twig', array(
        'results' => $results,
    ));
})
->bind('admin_form_manager_results')
;

$formManagerController->post('/{id}/remove/field', function (Request $request, $id) use ($app) {



})
->bind('admin_form_manager_remove_field')
;

$formManagerController->assert('_locale', implode('|', $app['languages']));

return $formManagerController;
