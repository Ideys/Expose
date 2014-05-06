<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentFactory;
use Ideys\Content\ContentTrait;
use Ideys\Content\SectionType;
use Symfony\Component\Form\FormFactory;
use Doctrine\DBAL\Connection;

/**
 * Sections prototype class.
 */
abstract class Section
{
    use ContentTrait;

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
        'language' => null,
        'archive' => '0',
    );

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @var array
     */
    protected $sections = array();

    /**
     * @var string
     */
    protected $language = 'en';

    /**
     * Visibility states.
     */
    const VISIBILITY_HOMEPAGE   = 'homepage';
    const VISIBILITY_PUBLIC     = 'public';
    const VISIBILITY_PRIVATE    = 'private';
    const VISIBILITY_HIDDEN     = 'hidden';
    const VISIBILITY_CLOSED     = 'closed';

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     * @param array                     $entity
     */
    public function __construct(Connection $db, array $entity = array())
    {
        $this->db = $db;
        $this->attributes = array_merge($this->attributes, $entity);
        $this->parameters = (array) unserialize($this->attributes['parameters']);
    }

    /**
     * Add a child section to section.
     *
     * @param Section $section
     *
     * @return Section
     */
    public function addSection(Section $section)
    {
        $this->sections[] = $section;

        return $this;
    }

    /**
     * Return section child sections.
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Return section items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Return section item found by slug.
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
     * Method extended by section children.
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
     * @param string $language
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
        return self::VISIBILITY_PRIVATE === $this->visibility;
    }

    /**
     * Define if content is not accessible.
     *
     * @return boolean
     */
    public function isClosed()
    {
        return self::VISIBILITY_CLOSED === $this->visibility;
    }

    /**
     * Define if the section is the homepage.
     *
     * @return boolean
     */
    public function isHomepage()
    {
        return self::VISIBILITY_HOMEPAGE === $this->visibility;
    }

    /**
     * Define if the section is archived.
     *
     * @return boolean
     */
    public function isArchived()
    {
        return 1 == $this->archive;
    }

    /**
     * Define if the section have to be displayed into menu.
     *
     * @param boolean $userHasCredentials
     *
     * @return boolean
     */
    public function isMenuEnabled($userHasCredentials = false)
    {
        return !$this->isArchived()
            && !in_array($this->visibility, array(
                self::VISIBILITY_HOMEPAGE,
                self::VISIBILITY_HIDDEN,
                self::VISIBILITY_CLOSED
            ))
            && (
                ($this->visibility !== self::VISIBILITY_PRIVATE)
              || $userHasCredentials);
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
    public function hydrateItems()
    {
        $sql =
           'SELECT i.*, t.title, t.description, t.content,
            t.link, t.parameters, t.language
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
