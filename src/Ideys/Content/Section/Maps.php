<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentFactory;
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
        if (empty($this->connectedSectionsId)) {
            $entities = array();
        } else {
            $entities = $this->db
                ->fetchAll(
                    ContentFactory::getSqlSelectItem().
                    'WHERE s.id IN  ('.implode(',', $this->connectedSectionsId).') '.
                    'ORDER BY s.hierarchy, i.hierarchy ');
        }

        // Connect related places to linked items
        $items = array();
        foreach ($entities as $item) {
            $items[$item['id']] = $item + array('place' => array());
        }
        foreach ($this->getItems('Place') as $place) {
            if (array_key_exists($place->parent_id, $items)) {
                $items[$place->parent_id]['place'] = $place;
            }
        }

        return $items;
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
