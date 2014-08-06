<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentFactory;
use Ideys\Content\Item\Item;
use Ideys\Content\Item\Place;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Form as SfForm;

/**
 * Maps content manager.
 */
class Maps extends Section implements SectionInterface
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
     * Hold linked sections items
     *
     * @var array
     */
    private $linkedItems = array();

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
     * Return all linkable sections to a Map section.
     * Exclude other Maps sections and Dir sections.
     *
     * @return array
     */
    public function getLinkableSections()
    {
        return  $this->db
            ->fetchAll(
                'SELECT s.id, s.expose_section_id, '.
                's.type, s.slug, s.visibility, '.
                't.title, t.description, t.legend, t.parameters '.
                'FROM expose_section AS s '.
                'LEFT JOIN expose_section_trans AS t '.
                'ON t.expose_section_id = s.id '.
                'WHERE s.type NOT IN  (\'dir\', \'maps\') '.
                'AND s.archive = 0 '.
                'ORDER BY s.hierarchy ');
    }

    /**
     * Return linked Sections Items.
     *
     * @return array
     */
    public function getLinkedSectionsItems()
    {
        if (empty($this->linkedItems)) {
            if (empty($this->connectedSectionsId)) {
                $entities = array();
            } else {
                $entities = $this->db
                    ->fetchAll(
                        ContentFactory::getSqlSelectItem().
                        'WHERE s.id IN  ('.implode(',', $this->connectedSectionsId).') '.
                        'ORDER BY s.hierarchy, i.hierarchy ');
            }

            foreach ($entities as $data) {
                $this->linkedItems[$data['id']] = ContentFactory::instantiateItem($data);
            }
        }

        return $this->linkedItems;
    }

    /**
     * Test if Map has some items with coordinates.
     *
     * @return boolean
     */
    public function hasPlacesDefined()
    {
        // Places items has always coordinates.
        if ($this->hasItems('Place')) {
            return true;
        }

        // Test if at least one linked sections item has coordinates defined.
        foreach ($this->getLinkedSectionsItems() as $linkedItem)
        if ($linkedItem->hasCoordinates()) {
            return true;
        }

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
     *
     * @param FormFactory $formFactory
     * @param Place       $place
     *
     * @return SfForm
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
        ;
        $this->coordinatesFields($formBuilder);

        return $formBuilder->getForm();
    }

    /**
     * Coordinates form for all items types.
     *
     * @param FormFactory $formFactory
     * @param Item        $item
     *
     * @return SfForm
     */
    public function coordinatesForm(FormFactory $formFactory, Item $item)
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
     * @return Maps
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
     * @return Maps
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
     * @return Maps
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
     * @return Maps
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
