<?php

use Ideys\SilexHooks;
use Ideys\Content\Section;
use Ideys\Content\Type;
use Ideys\Content\ContentFactory;
use Ideys\Settings\Settings;
use Symfony\Component\HttpFoundation\Request;

$contentManagerController = SilexHooks::controllerFactory($app);

$contentManagerController->match('/', function (Request $request) use ($app) {

    $contentFactory = new ContentFactory($app);
    $sectionType = new Type\SectionType($app['db'], $app['form.factory']);
    $settings = new Settings($app['db']);

    $newSection = new Section\Gallery($app['db']);
    $newSection->setVisibility($settings->newSectionDefaultVisibility);
    $form = $sectionType->createForm($newSection);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->addSection($newSection);
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
        $app['db']->update('expose_section',
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
//    $app['monolog']->addDebug(var_export($hierarchy, true));

    foreach ($hierarchy as $key => $value) {
        $app['db']->update('expose_section_item',
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

    $itemIds = $request->get('items');
    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $response = array();
    foreach ($itemIds as $id) {
        if ($section->attachItem($id)) {
            $response[] = $id;
        }
    }

    return $app->json($response);
})
->assert('id', '\d+')
->bind('admin_content_manager_move_items')
;

$contentManagerController->post('/toggle/items/{id}', function (Request $request, $id) use ($app) {

    $itemIds = $request->get('items');
    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $response = array();
    foreach ($section->getItems('Item') as $item) {
        if (in_array($item->id, $itemIds)) {
            $item->toggle();
            $app['db']->update('expose_section_item',
                array('published' => $item->published),
                array('id' => $item->id)
            );
            $response[] = $item->id;
        }
    }

    return $app->json($response);
})
->assert('id', '\d+')
->bind('admin_content_manager_toggle_items')
;

$contentManagerController->get('/{id}/archive', function ($id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $contentFactory->switchArchive($id);

    return SilexHooks::redirect($app, 'admin_content_manager');
})
->assert('id', '\d+')
->bind('admin_content_manager_archive')
;

$contentManagerController->match('/{id}/edit/dir', function (Request $request, $id) use ($app) {

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $dirType = new Type\SectionDirType($app['db'], $app['form.factory']);
    $form = $dirType->editForm($section);

    $form->handleRequest($request);
    if ($form->isValid()) {
        $contentFactory->updateSection($section);
        return SilexHooks::redirect($app, 'admin_content_manager');
    }

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

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

    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    $editForm = $section->settingsForm($app['form.factory']);
    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();

    $editForm->handleRequest($request);
    if ($editForm->isValid()) {
        $contentFactory->updateSection($section);
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

    $deleteForm = $app['form.factory']->createBuilder('form')->getForm();
    $contentFactory = new ContentFactory($app);
    $section = $contentFactory->findSection($id);

    // For directories need to have full sections tree
    if (Section\Section::SECTION_DIR == $section->getType()) {
        $sections = $contentFactory->findSections();
        $section = $sections[$section->getId()];
    }

    $deleteForm->handleRequest($request);
    if ($deleteForm->isValid()) {
        $section->delete();

        SilexHooks::session($app)
            ->getFlashBag()
            ->add('default', $app['translator']->trans($section->getType() . '.deleted'));
    }

    return SilexHooks::redirect($app, 'admin_content_manager');
})
->assert('id', '\d+')
->bind('admin_content_manager_delete')
;

$contentManagerController->assert('_locale', implode('|', $app['languages']));

return $contentManagerController;
