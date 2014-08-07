<?php

namespace Ideys\Content\Type;

use Ideys\Content\Section;
use Ideys\Settings\Settings;

/**
 * Section Gallery type.
 */
class SectionGalleryType extends SectionType
{
    /**
     * Return section form builder.
     *
     * @param \Ideys\Content\Section\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section\Section $section)
    {
        $formBuilder = parent::formBuilder($section)
            ->remove('type')
            ->add('gallery_mode', 'choice', array(
                'label' => 'gallery.mode.mode',
                'choices' => Section\Gallery::getGalleryModeChoice(),
            ))
            ->add('slide_mode', 'choice', array(
                'label' => 'gallery.slide.mode',
                'choices' => Section\Gallery::getSlideModeChoice(),
            ))
            ->add('extended', 'choice', array(
                'label' => 'gallery.mode.full.screen.extended',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('nav_bar', 'choice', array(
                'label' => 'gallery.nav.bar.display',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('thumb_list', 'choice', array(
                'label' => 'gallery.thumb.list.display',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('grid_rows', 'choice', array(
                'label' => 'gallery.grid.rows',
                'choices' => Section\Gallery::getGalleryGridRowsChoice(),
            ))
            ->add('grid_rows_medium', 'choice', array(
                'label' => 'gallery.grid.rows.medium',
                'choices' => Section\Gallery::getGalleryGridRowsChoice(8),
            ))
            ->add('grid_rows_small', 'choice', array(
                'label' => 'gallery.grid.rows.small',
                'choices' => Section\Gallery::getGalleryGridRowsChoice(3),
            ))
        ;

        return $formBuilder;
    }
}
