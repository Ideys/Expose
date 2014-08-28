<?php

namespace Ideys\Content\Item\Entity;

use Ideys\Content\AbstractEntity;

/**
 * Items prototype class.
 */
abstract class Item extends AbstractEntity
{
    /**
     * @var integer
     */
    protected $exposeSectionId;

    /**
     * @var string
     */
    protected $type;

    const ITEM_SLIDE    = 'slide';
    const ITEM_VIDEO    = 'video';
    const ITEM_PAGE     = 'page';
    const ITEM_POST     = 'post';
    const ITEM_FIELD    = 'field';
    const ITEM_PLACE    = 'place';

    /**
     * @var string
     */
    protected $category;

    /**
     * @var string
     */
    protected $tags;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var string
     */
    protected $language;

    /**
     * @var \DateTime
     */
    protected $postingDate;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $published = '1';

    /**
     * @var integer
     */
    protected $hierarchy = 0;

    /**
     * Set type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return item types keys.
     *
     * @return array
     */
    public static function getTypes()
    {
        return array(
            self::ITEM_SLIDE,
            self::ITEM_VIDEO,
            self::ITEM_PAGE,
            self::ITEM_POST,
            self::ITEM_FIELD,
            self::ITEM_PLACE,
        );
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set tags
     *
     * @param string $tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string|null
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
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

    /**
     * Set parameters
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->parameters = empty($parameters)
            ? array() : $parameters;

        return $this;
    }

    /**
     * Add a parameter
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addParameter($key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return (array) $this->parameters;
    }

    /**
     * Get a parameter
     *
     * @param string $key
     *
     * @return mixed
     */
    public function retrieveParameter($key)
    {
        return array_key_exists($key, $this->getParameters())
            ? $this->parameters[$key] : null;
    }

    /**
     * Set language
     *
     * @param string $language
     *
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set postingDate
     *
     * @param \DateTime $postingDate
     *
     * @return $this
     */
    public function setPostingDate(\DateTime $postingDate)
    {
        $this->postingDate = $postingDate;

        return $this;
    }

    /**
     * Get postingDate
     *
     * @return \DateTime
     */
    public function getPostingDate()
    {
        return $this->postingDate;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set published
     *
     * @param string $published
     *
     * @return $this
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return string
     */
    public function getPublished()
    {
        return $this->published;
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
     * Set hierarchy
     *
     * @param string $hierarchy
     *
     * @return $this
     */
    public function setHierarchy($hierarchy)
    {
        $this->hierarchy = $hierarchy;

        return $this;
    }

    /**
     * Get hierarchy
     *
     * @return string
     */
    public function getHierarchy()
    {
        return $this->hierarchy;
    }
}
