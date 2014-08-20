<?php

namespace Ideys\Content\Item\Provider;

use Ideys\Content\Item\Entity;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Form;

/**
 * Place item provider.
 */
class PlaceProvider extends ItemProvider
{
    /**
     * New place form.
     *
     * @param FormFactory $formFactory
     * @param Entity\Place  $place
     *
     * @return Form
     */
    public function addPlaceForm(FormFactory $formFactory, Entity\Place $place)
    {
        $formBuilder = $formFactory->createBuilder('form', $place)
            ->add('title', 'text', array(
                'label' => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
        ;
        $this->coordinatesFields($formBuilder);

        return $formBuilder->getForm();
    }

    /**
     * Coordinates form for all items types.
     *
     * @param FormFactory $formFactory
     * @param Entity\Item   $item
     *
     * @return Form
     */
    public function coordinatesForm(FormFactory $formFactory, Entity\Item $item)
    {
        $formBuilder = $formFactory->createBuilder('form', $item);
        $this->coordinatesFields($formBuilder);

        return $formBuilder->getForm();
    }

    /**
     * Coordinates fields.
     *
     * @param FormBuilderInterface $formBuilder
     *
     * @return FormBuilderInterface
     */
    private function coordinatesFields(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('latitude', 'number', array(
                'label' => 'maps.latitude',
                'precision' => 15,
            ))
            ->add('longitude', 'number', array(
                'label' => 'maps.longitude',
                'precision' => 15,
            ))
        ;

        return $formBuilder;
    }
}
