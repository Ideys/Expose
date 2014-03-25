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
     * Section main attributes
     *
     * @var array
     */
    protected $attributes = array(
        'id' => null,
        'expose_section_id' => null,
        'type' => null,
        'title' => null,
        'description' => null,
        'total_items' => 0,
        'custom_css' => null,
        'custom_js' => null,
        'parameters' => 'N;',
        'menu_pos' => 'main',
        'visibility' => 'public',
        'homepage' => '0',
        'language' => null,
    );

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
     * @param \Doctrine\DBAL\Connection $db
     * @param array                     $entity
     */
    public function __construct(\Doctrine\DBAL\Connection $db, array $entity = array())
    {
        $this->db = $db;
        $this->attributes = array_merge($this->attributes, $entity);
        $this->parameters = (array) unserialize($this->attributes['parameters']);

        $this->hydrateItems();
    }

    /**
     * Return section items.
     *
     * @param string $name
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Return section item finded by slug.
     *
     * @param string $slug
     *
     * @return \Ideys\Content\Item\Item|false
     */
    public function getItemFromSlug($slug)
    {
        foreach ($this->items as $item) {
            if ($slug == $item->slug) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Define if the section has more than one page.
     * Method extended by section childs.
     *
     * @return boolean
     */
    public function hasMultiplePages()
    {
        return false;
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
     * Define if the section is the homepage.
     *
     * @return boolean
     */
    public function isHomepage()
    {
        return 1 == $this->homepage;
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

    /**
     * Delete a section item.
     *
     * @param integer $id
     *
     * @return boolean
     */
    public function deleteItem($id)
    {
        // Delete item's translations
        $this->db->delete('expose_section_item_trans', array('expose_section_item_id' => $id));
        // Delete item
        $rows = $this->db->delete('expose_section_item', array('id' => $id));

        return (0 < $rows);
    }

    /**
     * Attach an item from another section to this section.
     *
     * @param integer $id The item id
     *
     * @return boolean
     */
    public function attachItem($id)
    {
        $affectedRows = $this->db->update('expose_section_item',
                array('expose_section_id' => $this->id),
                array('id' => $id)
        );

        return (boolean) $affectedRows;
    }

    /**
     * Delete section and this items in database.
     *
     * @return boolean
     */
    public function delete()
    {
        // Delete section items
        foreach ($this->items as $item) {
            $this->deleteItem($item->id);
        }

        // Delete section's translations
        $this->db->delete('expose_section_trans', array('expose_section_id' => $this->id));
        // Delete section
        $rows = $this->db->delete('expose_section', array('id' => $this->id));

        return (0 < $rows);
    }
}
