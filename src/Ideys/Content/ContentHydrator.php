<?php

namespace Ideys\Content;

use Ideys\Content\Section;
use Ideys\Content\Item;

/**
 * Content hydrator service.
 */
class ContentHydrator
{
    /**
     * Return instantiated Section from array data.
     *
     * @param array                     $data
     *
     * @return Section\Section
     *
     * @throws \Exception If Section type is unknown
     */
    public static function instantiateSection($data)
    {
        switch ($data['type']) {
            case Section\Section::SECTION_GALLERY:
                $section = new Section\Gallery();
                break;
            case Section\Section::SECTION_HTML:
                $section = new Section\Html();
                break;
            case Section\Section::SECTION_BLOG:
                $section = new Section\Blog();
                break;
            case Section\Section::SECTION_FORM:
                $section = new Section\Form();
                break;
            case Section\Section::SECTION_CHANNEL:
                $section = new Section\Channel();
                break;
            case Section\Section::SECTION_MAP:
                $section = new Section\Map();
                break;
            case Section\Section::SECTION_LINK:
                $section = new Section\Link();
                break;
            case Section\Section::SECTION_DIR:
                $section = new Section\Dir();
                break;
            default:
                throw new \Exception(sprintf('Unable to find a Section of type "%s".', $data['type']));
        }

        static::hydrator($section, $data);

        return $section;
    }

    /**
     * Return instantiated Item from array data.
     *
     * @param array     $data
     *
     * @return \Ideys\Content\Item\Item
     */
    public static function instantiateItem($data)
    {
        if (!in_array($data['type'], Item\Item::getTypes())) {
            $data['type'] = static::getDefaultSectionItemType($data['section_type']);
        }

        $itemClassName = '\Ideys\Content\Item\\'.ucfirst($data['type']);
        $itemClass = new $itemClassName();

        static::hydrator($itemClass, $data);

        return $itemClass;
    }

    /**
     * @param object $object
     * @param array  $data
     */
    private static function hydrator(&$object, $data)
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
     * Return item types keys.
     *
     * @param  string $type The section type
     *
     * @return string       The section default item
     */
    public static function getDefaultSectionItemType($type)
    {
        $sectionTypes = Section\Section::getTypes();
        $sectionTypes = array_diff($sectionTypes, array(Section\Section::SECTION_LINK, Section\Section::SECTION_DIR));
        $itemTypes = Item\Item::getTypes();
        $sectionItems = array_combine($sectionTypes, $itemTypes);

        return $sectionItems[$type];
    }

    /**
     * Instantiate a related content object from database entity.
     *
     * @param array  $sectionTranslations
     * @param string $language
     *
     * @return Section\Section
     */
    public function hydrateSection(array $sectionTranslations, $language)
    {
        if (empty($sectionTranslations)) {
            return false;
        }

        $sectionData = $this->retrieveLanguage($sectionTranslations, $language);
        $section = static::instantiateSection($sectionData);
//        $this->hydrateItems($section);
        $section->setLanguage($language);

        return $section;
    }

    /**
     * Retrieve section in current language or fallback to default one.
     *
     * @param array  $translations
     * @param string $language
     *
     * @return string
     */
    private function retrieveLanguage(array $translations, $language)
    {
        foreach ($translations as $translation) {
            if ($translation['language'] == $language) {
                return $translation;
            }
        }

        return $translations[0];
    }
}
