<?php

use Ideys\Settings\Settings;
use Ideys\Settings\SettingsType;
use Symfony\Component\HttpFoundation\Request;

$siteSettingsController = $app['controllers_factory'];

$siteSettingsController->match('/', function (Request $request) use ($app) {

    $settings = new Settings($app['db']);
    $settingsType = new SettingsType($app['form.factory']);
    $form = $settingsType->form($settings->getAll());

    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $settings->updateParameters($data);
        $app['session']
            ->getFlashBag()
            ->add('success', $app['translator']->trans('site.settings.updated'));
    }

    return $app['twig']->render('backend/siteSettings/siteSettings.html.twig', array(
        'form' => $form->createView(),
    ));
})
->bind('admin_site_settings')
->method('GET|POST')
;

$siteSettingsController
->assert('_locale', implode('|', $app['languages']))
->secure('ROLE_ADMIN')
;

return $siteSettingsController;
