<?php

namespace Ideys\Content\Section\Type;

use Ideys\Content\Section\Entity\Section;
use Ideys\Content\Section\Entity\Map;

/**
 * Section Map type.
 */
class MapType extends SectionType
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
            ->add('zoom', 'choice', array(
                'label' => 'maps.zoom',
                'choices' => Map::getZoomChoice(),
            ))
            ->add('latitude', 'number', array(
                'label' => 'maps.latitude',
                'precision' => 15,
            ))
            ->add('longitude', 'number', array(
                'label' => 'maps.longitude',
                'precision' => 15,
            ))
            ->add('mapMode', 'choice', array(
                'label' => 'maps.mode.mode',
                'choices' => Map::getModeChoice(),
            ))
        ;

        return $formBuilder;
    }
}
