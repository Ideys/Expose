<?php

namespace Ideys\Content\Section;

use Ideys\Content\Item;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Form as SfForm;

/**
 * Map content manager.
 */
class Map extends Section implements SectionInterface
{
    /**
     * @param integer
     */
    private $zoom = 1;

    /**
     * @param integer
     */
    private $latitude = 0;

    /**
     * @param integer
     */
    private $longitude = 0;

    /**
     * @param string
     */
    private $mapMode = 'ROADMAP';

    /**
     * Hold linked sections items.
     *
     * @var array
     */
    private $linkedItems = array();

    /**
     * {@inheritdoc}
     */
    public function getDefaultItems()
    {
        return $this->getPlaces();
    }

    /**
     * Return Place Items.
     *
     * @return array
     */
    public function getPlaces()
    {
        return $this->getItemsOfType(Item\Item::ITEM_PLACE);
    }

    /**
     * Test if Map has some Place Items.
     *
     * @return boolean
     */
    public function hasPlaces()
    {
        return $this->hasItemsOfType(Item\Item::ITEM_PLACE);
    }

    /**
     * {@inheritdoc}
     */
    public function countMainItems()
    {
        return count($this->getPlaces());
    }

    /**
     * Test if Map has some Items with coordinates.
     *
     * @return boolean
     */
    public function hasPlacesWithCoordinates()
    {
        // Places items has always coordinates.
        if ($this->hasPlaces()) {
            return true;
        }

        // Test if at least one linked sections item has coordinates defined.
        foreach ($this->getLinkedSectionsItems() as $linkedItem)
            if ($linkedItem instanceof Item\Item
                && $linkedItem->hasCoordinates()) {
                return true;
            }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSlidesHolder()
    {
        return false;
    }

    /**
     * New place form.
     *
     * @param FormFactory $formFactory
     * @param Item\Place  $place
     *
     * @return SfForm
     */
    public function addPlaceForm(FormFactory $formFactory, Item\Place $place)
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
     * @param Item\Item   $item
     *
     * @return SfForm
     */
    public function coordinatesForm(FormFactory $formFactory, Item\Item $item)
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
     * @return mixed
     */
    public function getZoom()
    {
        return $this->zoom;
    }

    /**
     * @param mixed $zoom
     *
     * @return Map
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     *
     * @return Map
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     *
     * @return Map
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMapMode()
    {
        return $this->mapMode;
    }

    /**
     * @param mixed $mapMode
     *
     * @return Map
     */
    public function setMapMode($mapMode)
    {
        $this->mapMode = $mapMode;

        return $this;
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
