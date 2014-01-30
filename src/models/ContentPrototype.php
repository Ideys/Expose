<?php

/**
 * Contents parent class.
 */
abstract class ContentPrototype
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @var string
     */
    protected $language = 'en';

    /**
     * Constructor.
     *
     * @param array $entity
     */
    public function __construct(\Doctrine\DBAL\Connection $db, array $entity)
    {
        $this->db = $db;
        $this->attributes = $entity;
        $this->hydrateItems();
    }

    /**
     * Magic method get, return attributes.
     *
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return $this->attributes[$name];
    }

    public function __call($name, $parameters)
    {
        if (array_key_exists($name, (array)$this->attributes)) {
            return $this->attributes[$name];
        } elseif (array_key_exists($name, (array)$this->attributes['parameters'])) {
            return $this->attributes['parameters'][$name];
        } elseif (array_key_exists($name, static::getParameters())) {
            return static::getParameters()[$name];
        }
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, (array)$this->attributes)) {
            $this->attributes[$name] = $value;
        } elseif (array_key_exists($name, (array)$this->attributes['parameters'])) {
            $this->attributes['parameters'][$name] = $value;
        } elseif (array_key_exists($name, static::getParameters())) {
            $this->attributes['parameters'][$name] = $value;
        }
    }

    /**
     * Return section items.
     *
     * @param string $name
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Define content translation language.
     *
     * @param string $name
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Define if content is hidden from anonymous users.
     *
     * @return boolean
     */
    public function isPrivate()
    {
        return 'private' === $this->visibility;
    }

    /**
     * Define if content is not accessible.
     *
     * @return boolean
     */
    public function isClosed()
    {
        return 'closed' === $this->visibility;
    }

    /**
     * Define if content has some items or not.
     *
     * @return boolean
     */
    public function hasItems()
    {
        return count($this->items) > 0;
    }

    /**
     * Fill items attribute with section's persisted items.
     */
    private function hydrateItems()
    {
        $sql =
           'SELECT i.*, t.title, t.description, t.content, t.parameters
            FROM expose_section_item AS i
            LEFT JOIN expose_section_item_trans AS t
            ON t.expose_section_item_id = i.id
            WHERE i.expose_section_id = ?
            AND t.language = ?
            ORDER BY i.hierarchy ASC';
        $entities = $this->db->fetchAll($sql, array($this->id, $this->language));

        foreach ($entities as $entity) {
            $this->items[$entity['id']] = $entity;
        }
    }
}
