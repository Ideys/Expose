<?php

namespace Ideys\Content\Section\Type;

use Ideys\Content\Section\Entity\Section;
use Ideys\Content\Section\Entity\Gallery;
use Ideys\Settings\Settings;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Section Gallery type.
 */
class GalleryType extends SectionType
{
    /**
     * Return section form builder.
     *
     * @param \Ideys\Content\Section\Entity\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section $section)
    {
        $formBuilder = parent::formBuilder($section)
            ->remove('type')
            ->add('galleryMode', ChoiceType::class, array(
                'label' => 'gallery.mode.mode',
                'choices' => Gallery::getGalleryModeChoice(),
            ))
            ->add('slideMode', ChoiceType::class, array(
                'label' => 'gallery.slide.mode',
                'choices' => Gallery::getSlideModeChoice(),
            ))
            ->add('size', ChoiceType::class, array(
                'label' => 'gallery.slide.size.size',
                'choices' => Gallery::getSizeChoice(),
            ))
            ->add('navBar', ChoiceType::class, array(
                'label' => 'gallery.nav.bar.display',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('thumbList', ChoiceType::class, array(
                'label' => 'gallery.thumb.list.display',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('gridRows', ChoiceType::class, array(
                'label' => 'gallery.grid.rows',
                'choices' => Gallery::getGalleryGridRowsChoice(),
            ))
            ->add('gridRowsMedium', ChoiceType::class, array(
                'label' => 'gallery.grid.rows.medium',
                'choices' => Gallery::getGalleryGridRowsChoice(8),
            ))
            ->add('gridRowsSmall', ChoiceType::class, array(
                'label' => 'gallery.grid.rows.small',
                'choices' => Gallery::getGalleryGridRowsChoice(3),
            ))
        ;

        return $formBuilder;
    }
}
