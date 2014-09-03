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
                'label' => 'map.zoom',
                'choices' => Map::getZoomChoice(),
            ))
            ->add('latitude', 'number', array(
                'label' => 'map.latitude',
                'precision' => 15,
            ))
            ->add('longitude', 'number', array(
                'label' => 'map.longitude',
                'precision' => 15,
            ))
            ->add('mapMode', 'choice', array(
                'label' => 'map.mode.mode',
                'choices' => Map::getMapModeChoice(),
            ))
        ;

        return $formBuilder;
    }
}
