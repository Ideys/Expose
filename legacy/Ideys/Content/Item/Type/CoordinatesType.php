<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Item\Entity\Item;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Coordinates for items type.
 */
class CoordinatesType extends ItemType
{
    /**
     * Coordinates form for all items types.
     *
     * @param Item $item
     *
     * @return FormBuilderInterface
     */
    public function formBuilder(Item $item)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $item);

        static::coordinatesFields($formBuilder);

        return $formBuilder;
    }

    /**
     * Coordinates fields.
     *
     * @param FormBuilderInterface $formBuilder
     *
     * @return FormBuilderInterface
     */
    public static function coordinatesFields(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('latitude', 'number', array(
                'label' => 'map.latitude',
                'precision' => 15,
            ))
            ->add('longitude', 'number', array(
                'label' => 'map.longitude',
                'precision' => 15,
            ))
        ;

        return $formBuilder;
    }
}
