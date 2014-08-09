<?php

use Ideys\SilexHooks;
use Ideys\Settings;
use Symfony\Component\HttpFoundation\Request;

$siteSettingsController = SilexHooks::controllerFactory($app);

$siteSettingsController->match('/', function (Request $request) use ($app) {

    $settingsProvider = new Settings\SettingsProvider($app['db']);
    $settings = $settingsProvider->getSettings();

    $settingsType = new Settings\SettingsType($app['form.factory']);
    $form = $settingsType->form($settings);

    $form->handleRequest($request);

    if ($form->isValid()) {
        $settingsProvider->persistSettings($settings);
        SilexHooks::session($app)
            ->getFlashBag()
            ->add('success', SilexHooks::translator($app)->trans('site.settings.updated'));
    }

    return SilexHooks::twig($app)->render('backend/siteSettings/siteSettings.html.twig', array(
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
