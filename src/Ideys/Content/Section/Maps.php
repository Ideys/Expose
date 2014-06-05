<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;
use Ideys\Content\SectionInterface;
use Ideys\Content\Item\Place;
use Symfony\Component\Form\FormFactory;

/**
 * Maps content manager.
 */
class Maps extends Section implements ContentInterface, SectionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array(
            'zoom'      => 1,
            'latitude'  => 0,
            'longitude' => 0,
            'map_mode'  => 'ROADMAP',
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultItemType()
    {
        return 'Place';
    }

    /**
     * {@inheritdoc}
     */
    public function isSlidesHolder()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function settingsForm(FormFactory $formFactory)
    {
        $formBuilder = $this->settingsFormBuilder($formFactory)
            ->add('zoom', 'choice', array(
                'label' => 'maps.zoom',
                'choices' => $this->getZoomChoice(),
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
                'choices' => $this->getModeChoice(),
            ))
        ;

        return $formBuilder->getForm();
    }

    /**
     * New place form.
     */
    public function addPlaceForm(FormFactory $formFactory, Place $place)
    {
        $formBuilder = $formFactory->createBuilder('form', $place)
            ->add('title', 'text', array(
                'label' => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
            ->add('latitude', 'number', array(
                'label' => 'maps.latitude',
                'precision' => 15,
            ))
            ->add('longitude', 'number', array(
                'label' => 'maps.longitude',
                'precision' => 15,
            ))
        ;

        return $formBuilder->getForm();
    }

    /**
     * Return maps zoom choices.
     *
     * @return array
     */
    public static function getZoomChoice()
    {
        return array_combine(range(1, 18), range(1, 18));
    }

    /**
     * Return maps mode choices.
     *
     * @return array
     */
    public static function getModeChoice()
    {
        return array(
            'HYBRID'    => 'maps.mode.hybrid',
            'ROADMAP'   => 'maps.mode.road.map',
            'SATELLITE' => 'maps.mode.satellite',
            'TERRAIN'   => 'maps.mode.terrain',
        );
    }
}
