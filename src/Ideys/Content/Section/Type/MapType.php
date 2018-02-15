<?php

namespace Ideys\Content\Section\Type;

use Ideys\Content\Section\Entity\Section;
use Ideys\Content\Section\Entity\Map;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

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
            ->add('zoom', ChoiceType::class, array(
                'label' => 'map.zoom',
                'choices' => Map::getZoomChoice(),
            ))
            ->add('latitude', NumberType::class, array(
                'label' => 'map.latitude',
                'scale' => 15,
            ))
            ->add('longitude', NumberType::class, array(
                'label' => 'map.longitude',
                'scale' => 15,
            ))
            ->add('mapMode', ChoiceType::class, array(
                'label' => 'map.mode.mode',
                'choices' => Map::getMapModeChoice(),
            ))
        ;

        return $formBuilder;
    }
}
