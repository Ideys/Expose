<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentFactory;
use Ideys\Content\SectionType;
use Symfony\Component\Form\FormFactory;

/**
 * Sections prototype class.
 */
abstract class Section
{
    use \Ideys\Content\ContentTrait;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

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

        if (is_string($entity['parameters'])) {
            $this->parameters = unserialize($entity['parameters']);
        } elseif (is_array($entity['parameters'])) {
            $this->parameters = $entity['parameters'];
        }

        $this->hydrateItems();
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
     * Return section settings form builder used to extends standard form.
     *
     * @param \Symfony\Component\Form\FormFactory $formFactory
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function settingsFormBuilder(FormFactory $formFactory)
    {
        $sectionType = new SectionType($this->db, $formFactory);

        $formBuilder = $sectionType->formBuilder($this);
        $formBuilder->remove('type');

        return $formBuilder;
    }

    /**
     * Return section settings form.
     *
     * @param \Symfony\Component\Form\FormFactory $formFactory
     *
     * @return \Symfony\Component\Form\Form
     */
    public function settingsForm(FormFactory $formFactory)
    {
        return $this->settingsFormBuilder($formFactory)->getForm();
    }

    /**
     * Fill items attribute with section's persisted items.
     */
    private function hydrateItems()
    {
        $sql =
           'SELECT i.*, t.title, t.description, t.content, t.parameters, t.language
            FROM expose_section_item AS i
            LEFT JOIN expose_section_item_trans AS t
            ON t.expose_section_item_id = i.id
            WHERE i.expose_section_id = ?
            ORDER BY i.hierarchy ASC';
        $itemTranslations = $this->db->fetchAll($sql, array($this->id));

        if (empty($itemTranslations)) {
            return false;
        }

        foreach ($itemTranslations as $itemData) {
            if (!in_array($itemData['type'], ContentFactory::getItemTypes())) {
                $itemData['type'] = ContentFactory::getDefaultSectionItemType($this->type);
            }
            $itemClass = '\Ideys\Content\Item\\'.ucfirst($itemData['type']);
            $this->items[$itemData['id']] = new $itemClass($itemData);
        }
    }
}
