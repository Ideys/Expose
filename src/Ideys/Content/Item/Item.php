<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentTrait;

/**
 * Items prototype class.
 */
abstract class Item
{
    use ContentTrait;

    /**
     * Item main attributes
     *
     * @var array
     */
    protected $attributes = array(
        'id' => null,
        'expose_section_id' => null,
        'type' => null,
        'category' => null,
        'title' => null,
        'description' => null,
        'content' => null,
        'link' => null,
        'path' => null,
        'latitude'  => null,
        'longitude' => null,
        'parameters' => '',
        'language' => null,
        'posting_date' => null,
        'author' => null,
        'published' => '1',
        'hierarchy' => 0,
    );

    /**
     * Constructor.
     *
     * @param array $entity
     */
    public function __construct(array $entity = array())
    {
        $this->attributes = array_merge($this->attributes, $entity);
        $this->attributes['posting_date'] = empty($this->attributes['posting_date'])
            ? null : new \DateTime($this->attributes['posting_date']);
        $this->parameters = (array) unserialize($this->attributes['parameters']);
    }

    /**
     * Toggle item visibility.
     */
    public function toggle()
    {
        $this->published = !$this->published;
    }

    /**
     * Test if item is published.
     *
     * @return boolean
     */
    public function isPublished()
    {
        return ($this->published == '1');
    }

    /**
     * Test if Item has latitude and longitude defined.
     *
     * @return boolean
     */
    public function hasCoordinates()
    {
        return ($this->latitude  != null)
           and ($this->longitude != null);
    }
}
