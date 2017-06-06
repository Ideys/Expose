<?php

namespace Ideys\Content;

use Ideys\SilexHooks;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types as DbTypes;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Security\Core\User\User;
use Silex\Application as SilexApp;

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
     * @param \Silex\Application $app
     */
    public function __construct(SilexApp $app)
    {
        $this->db = SilexHooks::db($app);
        $this->security = SilexHooks::security($app);
        $this->language = SilexHooks::language($app);
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
     * @param mixed $object
     * @param array $data
     */
    protected static function hydrate($object, $data)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf('The parameter $object must be an object, %s given.', gettype($object)));
        }

        $class = new \ReflectionClass($object);

        do {
            foreach ($class->getProperties() as $property) {
                $propertyName = $property->getName();
                $setterName = 'set' . ucfirst($propertyName);
                $columnName = Inflector::tableize($propertyName);

                if ($class->hasMethod($setterName)
                    && array_key_exists($columnName, $data)) {

                    $value = static::convertValue($data[$columnName], $property->getDocComment());
                    $object->{$setterName}($value);
                }
            }
        } while ($class = $class->getParentClass());
    }

    /**
     * Convert an entity to an array relative to database table schema.
     *
     * @param string         $tableName
     * @param AbstractEntity $entity
     *
     * @return array
     */
    protected function objectToArray($tableName, AbstractEntity $entity)
    {
        // Extract table columns
        $sectionColumns = $this->db
            ->getSchemaManager()
            ->listTableColumns(TABLE_PREFIX.$tableName);

        unset($sectionColumns['id']);

        // Filter class property with same name than table columns
        $data = array();
        $class = new \ReflectionClass($entity);

        foreach ($class->getProperties() as $property) {

            $propertyName = $property->getName();
            $getterName = 'get' . ucfirst($propertyName);
            $columnName = Inflector::tableize($propertyName);

            if ($class->hasMethod($getterName)
                && array_key_exists($columnName, $sectionColumns)) {

                $value = $entity->{$getterName}();
                $data[$columnName] = static::revertValue($value, $sectionColumns[$columnName]);
            }
        }

        return $data;
    }

    /**
     * Tinny ORM : convert database data to object data.
     *
     * @param mixed  $tableValue
     * @param string $docComment
     *
     * @return mixed
     */
    protected static function convertValue($tableValue, $docComment)
    {
        $objectValue = null;

        switch (true) {
            case strpos($docComment, '@var \DateTime') :
                $objectValue = new \DateTime($tableValue);
                break;
            case strpos($docComment, '@var boolean') :
                $objectValue = (bool) $tableValue;
                break;
            case strpos($docComment, '@var array') :
                $objectValue = unserialize($tableValue);
                $objectValue = is_array($objectValue) ? $objectValue : array();
                break;
            default:
                $objectValue = $tableValue;
        }

        return $objectValue;
    }

    /**
     * Tinny ORM : convert object data to database data.
     *
     * @param mixed  $objectValue
     * @param Column $column
     *
     * @return mixed
     */
    protected static function revertValue($objectValue, Column $column)
    {
        $tableValue = null;

        switch (true) {
            case $column->getType() instanceof DbTypes\DateTimeType :
                if ($objectValue instanceof \DateTime) {
                    $tableValue = $objectValue->format('Y-m-d H:i:s');
                }
                break;
            case $column->getType() instanceof DbTypes\BooleanType :
                $tableValue = (int) $objectValue;
                break;
            case $column->getType() instanceof DbTypes\ArrayType :
                $tableValue = serialize($objectValue);
                break;
            case $column->getType() instanceof DbTypes\ObjectType :
                $tableValue = serialize($objectValue);
                break;
            default:
                $tableValue = $objectValue;
        }

        return $tableValue;
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
                        'SELECT id FROM '.TABLE_PREFIX.'user '.
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
