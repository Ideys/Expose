<?php

namespace Ideys\Content\Type;

use Ideys\Content\Section;

/**
 * Section Maps type.
 */
class SectionMapsType extends SectionType
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
            ->add('zoom', 'choice', array(
                'label' => 'maps.zoom',
                'choices' => Section\Maps::getZoomChoice(),
            ))
            ->add('latitude', 'number', array(
                'label' => 'maps.latitude',
                'precision' => 15,
            ))
            ->add('longitude', 'number', array(
                'label' => 'maps.longitude',
                'precision' => 15,
            ))
            ->add('map_mode', 'choice', array(
                'label' => 'maps.mode.mode',
                'choices' => Section\Maps::getModeChoice(),
            ))
        ;

        return $formBuilder;
    }
}
