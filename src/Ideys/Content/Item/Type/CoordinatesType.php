<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Item\Entity\Item;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
            ->createBuilder(FormType::class, $item);

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
            ->add('latitude', NumberType::class, array(
                'label' => 'map.latitude',
                'scale' => 15,
            ))
            ->add('longitude', NumberType::class, array(
                'label' => 'map.longitude',
                'scale' => 15,
            ))
        ;

        return $formBuilder;
    }
}
