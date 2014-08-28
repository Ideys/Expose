<?php

namespace Ideys\Content;

use Doctrine\DBAL\Connection;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\SecurityContext;

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
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    protected $security;

    /**
     * Language of translated content.
     *
     * @var string
     */
    protected $language = 'en';

    /**
     * Constructor.
     *
     * @param Connection      $connection
     * @param SecurityContext $securityContext
     */
    public function __construct(Connection $connection, SecurityContext $securityContext)
    {
        $this->db = $connection;
        $this->security = $securityContext;
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

    /**
     * Convert an entity to an array relative to database table schema.
     *
     * @param AbstractEntity $entity
     * @param string         $tableName
     *
     * @return array
     */
    protected function objectToArray(AbstractEntity $entity, $tableName)
    {
        $data = array();

        // Extract table columns
        $sectionColumns = $this->db
            ->getSchemaManager()
            ->listTableColumns($tableName);

//        Inflector::tableize($attribute);

        return $data;
    }

    /**
     * Define user author and timestamp for persisted data.
     *
     * @param AbstractEntity $entity
     *
     * @return array
     */
    protected function blameAndTimestamp(AbstractEntity $entity)
    {
        $userId = null;
        $securityToken = $this->security->getToken();
        if (!empty($securityToken)) {
            $loggedUser = $securityToken->getUser();
            if ($loggedUser instanceof User) {
                $user = $this->db
                    ->fetchAssoc(
                        'SELECT id FROM expose_user '.
                        'WHERE username = ?',
                        array(
                            $loggedUser->getUsername(),
                        ));
                $userId = $user['id'];
            }
        }

        if ((int) $entity->getId() == 0) {
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy($userId);
        }
        $entity->setUpdatedAt(new \DateTime());
        $entity->setUpdatedBy($userId);
    }
}
