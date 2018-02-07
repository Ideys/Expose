<?php

namespace App\Content\Section;

use App\Content\Item\Item;
use App\Content\Item\Place;

/**
 * Map content manager.
 */
class Map extends Section implements SectionInterface
{
    /**
     * @param string
     */
    private $mapMode = self::MAP_MODE_ROAD_MAP;

    const MAP_MODE_HYBRID       = 'HYBRID';
    const MAP_MODE_ROAD_MAP     = 'ROADMAP';
    const MAP_MODE_SATELLITE    = 'SATELLITE';
    const MAP_MODE_TERRAIN      = 'TERRAIN';

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
        return $this->getItemsOfType(Item::ITEM_PLACE);
    }

    /**
     * Test if Map has some Place Items.
     *
     * @return boolean
     */
    public function hasPlaces()
    {
        return $this->hasItemsOfType(Item::ITEM_PLACE);
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
        foreach ($this->getConnectedSections() as $connectedSection) {
            foreach ($connectedSection->getItems() as $item) {
                if ($item->hasCoordinates()) {
                    return true;
                }
            }
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
     * @return integer
     */
    public function getZoom()
    {
        return $this->retrieveParameter('zoom', 1);
    }

    /**
     * @param mixed $zoom
     *
     * @return Map
     */
    public function setZoom($zoom)
    {
        $this->addParameter('zoom', $zoom);

        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->retrieveParameter('latitude', 0);
    }

    /**
     * @param float $latitude
     *
     * @return Map
     */
    public function setLatitude($latitude)
    {
        $this->addParameter('latitude', $latitude);

        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->retrieveParameter('longitude', 0);
    }

    /**
     * @param float $longitude
     *
     * @return Map
     */
    public function setLongitude($longitude)
    {
        $this->addParameter('longitude', $longitude);

        return $this;
    }

    /**
     * @return string
     */
    public function getMapMode()
    {
        return $this->mapMode;
    }

    /**
     * @param string $mapMode
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
    public static function getMapModeChoice()
    {
        return array(
            self::MAP_MODE_HYBRID    => 'map.mode.hybrid',
            self::MAP_MODE_ROAD_MAP  => 'map.mode.road.map',
            self::MAP_MODE_SATELLITE => 'map.mode.satellite',
            self::MAP_MODE_TERRAIN   => 'map.mode.terrain',
        );
    }
}
