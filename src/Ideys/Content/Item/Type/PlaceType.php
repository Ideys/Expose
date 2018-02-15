<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Item\Entity\Place;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Place item type.
 */
class PlaceType extends ItemType
{
    /**
     * New place form builder.
     *
     * @param Place $place
     *
     * @return FormBuilderInterface
     */
    public function formBuilder(Place $place)
    {
        $formBuilder = $this->formFactory
            ->createBuilder(FormType::class, $place)
            ->add('title', TextType::class, array(
                'label' => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
        ;

        CoordinatesType::coordinatesFields($formBuilder);

        return $formBuilder;
    }
}
