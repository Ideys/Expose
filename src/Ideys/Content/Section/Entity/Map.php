<?php

namespace Ideys\Content\Section\Entity;

use Ideys\Content\Item\Entity;
use Ideys\Content\Item\Entity\Place;

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
    private $mapMode = self::MAP_MODE_ROAD_MAP;

    const MAP_MODE_HYBRID       = 'HYBRID';
    const MAP_MODE_ROAD_MAP     = 'ROADMAP';
    const MAP_MODE_SATELLITE    = 'SATELLITE';
    const MAP_MODE_TERRAIN      = 'TERRAIN';

    /**
     * Hold linked sections items.
     *
     * @var array
     */
    private $linkedItems = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = Section::SECTION_MAP;
    }

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
     * @return Place[]
     */
    public function getPlaces()
    {
        return $this->getItemsOfType(Entity\Item::ITEM_PLACE);
    }

    /**
     * Test if Map has some Place Items.
     *
     * @return boolean
     */
    public function hasPlaces()
    {
        return $this->hasItemsOfType(Entity\Item::ITEM_PLACE);
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
            if ($linkedItem instanceof Entity\Item
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
            self::MAP_MODE_HYBRID    => 'maps.mode.hybrid',
            self::MAP_MODE_ROAD_MAP  => 'maps.mode.road.map',
            self::MAP_MODE_SATELLITE => 'maps.mode.satellite',
            self::MAP_MODE_TERRAIN   => 'maps.mode.terrain',
        );
    }
}
