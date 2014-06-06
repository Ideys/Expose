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
        'parent_id' => null,
        'type' => null,
        'category' => null,
        'title' => null,
        'description' => null,
        'content' => null,
        'link' => null,
        'path' => null,
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
     * Test if Item has a twin Item connected.
     *
     * @return boolean
     */
    public function isPaired()
    {
        return ((int) $this->parent_id) > 0;
    }
}
