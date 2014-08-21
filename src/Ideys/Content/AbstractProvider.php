<?php

namespace Ideys\Content;

use Doctrine\DBAL\Connection;

/**
 * Content provider global class.
 */
abstract class AbstractProvider
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * Language of translated content.
     *
     * @var string
     */
    protected $language = 'en';

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->db = $connection;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Hydrate Content class from array data to object.
     *
     * @param object $object
     * @param array  $data
     */
    protected static function hydrate(&$object, $data)
    {
        $class = new \ReflectionClass($object);

        do {
            foreach ($class->getProperties() as $property) {
                $propertyName = $property->getName();
                if ($class->hasMethod('get' . ucfirst($propertyName))
                    && array_key_exists($propertyName, $data)) {

                    $object->{'set' . ucfirst($propertyName)}($data[$propertyName]);
                }
            }
        } while ($class = $class->getParentClass());
    }
}
