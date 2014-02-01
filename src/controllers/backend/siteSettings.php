<?php

use Ideys\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$siteSettingsController = $app['controllers_factory'];

$siteSettingsController->match('/', function (Request $request) use ($app) {

    $settings = new Settings($app['db']);

    $form = $app['form.factory']->createBuilder('form', $settings->getAll())
        ->add('name', 'text', array(
            'constraints'   => array(
                new Assert\Length(array('min' => 3)),
                new Assert\NotBlank(),
            ),
            'label'         => 'site.name',
        ))
        ->add('description', 'textarea', array(
            'constraints'   => array(
                new Assert\Length(array('min' => 6)),
                new Assert\NotBlank(),
            ),
            'label'         => 'site.description',
        ))
        ->add('authorName', 'text', array(
            'constraints'   => array(
                new Assert\Length(array('min' => 3)),
                new Assert\NotBlank(),
            ),
            'label'         => 'site.author',
        ))
        ->add('analyticsKey', 'text', array(
            'constraints'   => array(
                new Assert\Length(array('min' => 6)),
            ),
            'required'      => false,
            'label'         => 'google.analytics.key',
        ))
        ->add('verificationKey', 'text', array(
            'constraints'   => array(
                new Assert\Length(array('min' => 6)),
            ),
            'required'      => false,
            'label'         => 'google.verification.key',
        ))
        ->add('layoutBackground', 'choice', array(
            'choices'   => array(
                'black' => 'site.background.black',
                'white' => 'site.background.white',
            ),
            'label'         => 'site.background.background',
        ))
        ->add('customStyle', 'textarea', array(
            'required'      => false,
            'label'         => 'site.style.custom',
        ))
        ->add('customJavascript', 'textarea', array(
            'required'      => false,
            'label'         => 'site.style.customjs',
        ))
        ->add('adminLink', 'choice', array(
            'choices'       => Settings::getAdminLinkChoices(),
            'label'         => 'admin.link',
        ))
        ->add('contactContent', 'textarea', array(
            'required'      => false,
            'label'         => 'contact.content',
        ))
        ->add('contactSection', 'choice', array(
            'choices'       => Settings::getContactSectionChoices(),
            'label'         => 'contact.section',
        ))
        ->add('menuPosition', 'choice', array(
            'choices'       => Settings::getMenuPositionChoices(),
            'label'         => 'site.menu.position',
        ))
        ->add('hideMenuOnHomepage', 'choice', array(
            'choices'       => Settings::getIOChoices(),
            'label'         => 'site.menu.hide.on.homepage',
        ))
        ->getForm();

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $settings->updateParameters($data);
    }

    return $app['twig']->render('backend/siteSettings/siteSettings.html.twig', array(
        'form' => $form->createView(),
    ));
})
->bind('admin_site_settings')
->method('GET|POST')
;

$siteSettingsController->assert('_locale', implode('|', $app['languages']));

return $siteSettingsController;
