<?php

use Ideys\SilexHooks;
use Ideys\Content\Section\Provider\DirProvider;
use Ideys\Content\Section\Provider\SectionProvider;
use Ideys\Content\Section\Entity\Section;
use Ideys\Content\Section\Type\SectionTypeFactory;
use Ideys\Settings\SettingsProvider;
use Symfony\Component\HttpFoundation\Request;

$contentManagerController = SilexHooks::controllerFactory($app);

$contentManagerController->match('/', function (Request $request) use ($app) {

    $typeFactory = new SectionTypeFactory($app['db'], $app['form.factory']);
    $sectionProvider = new SectionProvider($app);
    $settingsProvider = new SettingsProvider($app['db']);
    $settings = $settingsProvider->getSettings();

    $newSection = new Section();
    $newSection->setVisibility($settings->getNewSectionDefaultVisibility());
    $form = $typeFactory->createForm($newSection);

    $form->handleRequest($request);

    if ($form->isValid()) {
        $sectionProvider->persist($newSection);
        return SilexHooks::redirect($app, 'admin_content_manager', array(), '#panel'.$newSection->getId());
    }

    return SilexHooks::twig($app)->render('backend/contentManager/contentManager.html.twig', array(
        'form' => $form->createView(),
    ));
})
->bind('admin_content_manager')
->method('GET|POST')
;

$contentManagerController->match('/archives', function () use ($app) {

    return SilexHooks::twig($app)->render('backend/contentManager/archives.html.twig');
})
->bind('admin_content_manager_archives')
->method('GET')
;

$contentManagerController->post('/sort/sections', function (Request $request) use ($app) {

    $hierarchy = $request->get('hierarchy');

    foreach ($hierarchy as $key => $value) {
        SilexHooks::db($app)->update('expose_section',
                array('hierarchy' => $key),
                array('id' => filter_var($value, FILTER_SANITIZE_NUMBER_INT))
        );
    }
    $response = array(true);

    return $app->json($response);
})
->bind('admin_content_manager_sort_sections')
;

$contentManagerController->post('/sort/items', function (Request $request) use ($app) {

    $hierarchy = $request->get('hierarchy');

    foreach ($hierarchy as $key => $value) {
        SilexHooks::db($app)->update('expose_section_item',
                array('hierarchy' => $key),
                array('id' => filter_var($value, FILTER_SANITIZE_NUMBER_INT))
        );
    }
    $response = array(true);

    return $app->json($response);
})
->bind('admin_content_manager_sort_items')
;

$contentManagerController->post('/move/items/{id}', function (Request $request, $id) use ($app) {

    $sectionProvider = new SectionProvider($app);

    $section = $sectionProvider->find($id);
    $itemIds = $request->get('items');

    $response = array();
    foreach ($itemIds as $id) {
        if ($sectionProvider->attachItem($section, $id)) {
            $response[] = $id;
        }
    }

    return $app->json($response);
})
->assert('id', '\d+')
->bind('admin_content_manager_move_items')
;

$contentManagerController->post('/toggle/items/{id}', function (Request $request, $id) use ($app) {

    $db = SilexHooks::db($app);
    $sectionProvider = new SectionProvider($app);

    $section = $sectionProvider->find($id);
    $itemIds = $request->get('items');

    $response = array();

    foreach ($section->getItems('Item') as $item) {
        if (in_array($item->getId(), $itemIds)) {
            $item->toggle();
            $db->update('expose_section_item',
                array('published' => $item->isPublished()),
                array('id' => $item->getId())
            );
            $response[] = $item->getId();
        }
    }

    return $app->json($response);
})
->assert('id', '\d+')
->bind('admin_content_manager_toggle_items')
;

$contentManagerController->get('/{id}/archive', function ($id) use ($app) {

    $sectionProvider = new SectionProvider($app);

    $section = $sectionProvider->find($id);
    $section->toggleArchive();

    $sectionProvider->update($section);

    return SilexHooks::redirect($app, 'admin_content_manager');
})
->assert('id', '\d+')
->bind('admin_content_manager_archive')
;

$contentManagerController->match('/{id}/edit/dir', function (Request $request, $id) use ($app) {

    $dirProvider = new DirProvider($app);
    $section = $dirProvider->find($id);

    $typeFactory = new SectionTypeFactory($app['db'], $app['form.factory']);
    $form = $typeFactory->createForm($section);

    $form->handleRequest($request);

    if ($form->isValid()) {
        $dirProvider->update($section);
        return SilexHooks::redirect($app, 'admin_content_manager');
    }

    $deleteForm = SilexHooks::standardForm($app);

    return SilexHooks::twig($app)->render('backend/dirManager/_dirForm.html.twig', array(
        'section' => $section,
        'form' => $form->createView(),
        'delete_form' => $deleteForm->createView(),
    ));
})
->assert('id', '\d+')
->method('GET|POST')
->bind('admin_content_manager_edit_dir')
;

$contentManagerController->match('/{id}/settings', function (Request $request, $id) use ($app) {

    $sectionProvider = new SectionProvider($app);
    $section = $sectionProvider->find($id);

    $sectionTypeFactory = new SectionTypeFactory($app['db'], $app['form.factory']);
    $editForm = $sectionTypeFactory->createForm($section);
    $deleteForm = SilexHooks::standardForm($app);

    $editForm->handleRequest($request);
    if ($editForm->isValid()) {
        $sectionProvider->update($section);
        return SilexHooks::redirect($app, 'admin_content_manager', array(), '#panel'.$id);
    }

    return SilexHooks::twig($app)->render('backend/contentManager/_sectionSettings.html.twig', array(
        'edit_form' => $editForm->createView(),
        'delete_form' => $deleteForm->createView(),
        'section' => $section,
    ));
})
->assert('id', '\d+')
->bind('admin_content_manager_settings')
->method('GET|POST')
;

$contentManagerController->post('/{id}/delete', function (Request $request, $id) use ($app) {

    $sectionProvider = new SectionProvider($app);
    $section = $sectionProvider->find($id);

//    // For directories need to have full sections tree
//    if (Section::SECTION_DIR == $section->getType()) {
//        $sections = $contentFactory->findSections();
//        $section = $sections[$section->getId()];
//    }

    $deleteForm = SilexHooks::standardForm($app);
    $deleteForm->handleRequest($request);

    if ($deleteForm->isValid()) {
        $sectionProvider->delete($section);

        SilexHooks::flashMessage($app, $section->getType() . '.deleted');
    }

    return SilexHooks::redirect($app, 'admin_content_manager');
})
->assert('id', '\d+')
->bind('admin_content_manager_delete')
;

$contentManagerController->assert('_locale', implode('|', $app['languages']));

return $contentManagerController;
