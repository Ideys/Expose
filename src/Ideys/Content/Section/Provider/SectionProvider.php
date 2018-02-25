<?php

namespace Ideys\Content\Section\Provider;

use Ideys\Settings\Settings;
use Ideys\Content\AbstractProvider;
use Ideys\Content\Section\Entity\Section;
use Ideys\Content\Section\Entity\Html;
use Ideys\Content\Item\Provider\ItemProvider;
use Ideys\Content\Item\Entity\Page;
use Ideys\StringHelper;
use Silex\Application as SilexApp;

/**
 * Section provider global class.
 */
class SectionProvider extends AbstractProvider
{
    /**
     * @var \Ideys\Content\Item\Provider\ItemProvider
     */
    protected $itemProvider;

    public function __construct(SilexApp $app)
    {
        parent::__construct($app);

        $this->itemProvider = new ItemProvider($app);
    }

    /**
     * Return sections.
     *
     * @return array
     */
    public function findAll()
    {
        $sections = array();

        $sql = static::baseQuery()
            . 'WHERE t.language = ? '
            //. 'GROUP BY s.id '
            . 'ORDER BY s.hierarchy ASC ';
        $rows = $this->db->fetchAll($sql, array($this->language));

        // Use sql primary keys as array keys and objectify entity
        foreach ($rows as $data) {
            $sections[$data['id']] = $this->hydrateSection($data);
        }

        // Generate tree structure from raw data
        foreach ($sections as $id => $section) {
            if ($section instanceof Section) {
                $parentSectionId = $section->getExposeSectionId();
                if ($parentSectionId > 0) {
                    $sections[$parentSectionId]->addSection($section);
                    unset($sections[$id]);
                }
            }
        }

        return $sections;
    }

    /**
     * Return a section.
     *
     * @param integer $id
     *
     * @return Section
     */
    public function find($id)
    {
        $sql = static::baseQuery()
            . 'WHERE s.id = ? '
            . 'AND t.language = ? '
            . 'ORDER BY s.hierarchy ASC ';
        $data = $this->db->fetchAssoc($sql, array($id, $this->language));

        return $this->hydrateSection($data);
    }

    /**
     * Return a section.
     *
     * @param string $slug Section slug
     *
     * @return Section
     */
    public function findBySlug($slug)
    {
        $sql = static::baseQuery()
            . 'WHERE s.slug = ? '
            . 'AND t.language = ? '
            . 'ORDER BY s.hierarchy ASC ';
        $data = $this->db->fetchAssoc($sql, array($slug, $this->language));

        return $this->hydrateSection($data);
    }

    /**
     * Find the homepage section, create it if not exists.
     *
     * @return Section
     */
    public function findHomepage()
    {
        $sql = static::baseQuery()
            . 'WHERE s.visibility = ? '
            . 'AND t.language = ? '
            . 'ORDER BY s.hierarchy ASC ';

        $entities = $this->db->fetchAll($sql, array(
            Section::VISIBILITY_HOMEPAGE,
            $this->language
        ));

        // Generate default homepage
        if (empty($entities)) {
            $settings = new Settings($this->db);

            $homepage = new Html();
            $homepage->setTitle($settings->getName());
            $homepage->setVisibility(Section::VISIBILITY_HOMEPAGE);
            $this->persist($homepage);

            $page = new Page();
            $page->setExposeSectionId($homepage->getId());
            $page->setTitle($settings->getName());
            $page->setContent('<a href="__path:first_section__" id="homepage"><h1>'.$settings->getName().'</h1></a>');

            $this->itemProvider->create($homepage, $page);
        } else {

            $data = array_pop($entities);
            $homepage = $this->hydrateSection($data);
        }

        return $homepage;
    }

    /**
     * Return the first viewable section.
     *
     * @return Section|null
     */
    public function findFirstSection()
    {
        $sql = static::baseQuery()
            . "WHERE s.type NOT IN ('link', 'dir') "
            . "AND t.language = ? "
            . "AND s.visibility NOT IN ('homepage', 'closed') ";
        $entities = $this->db->fetchAll($sql, array($this->language));

        if (empty($entities)) {
            return null;
        }

        $data = array_pop($entities);

        return $this->hydrateSection($data);
    }

    /**
     * Instantiate a related content object from database entity.
     *
     * @param array   $data
     * @param boolean $hydrateConnectedSections
     *
     * @return Section|false
     */
    public function hydrateSection($data, $hydrateConnectedSections = true)
    {
        if (! $data) {
            return false;
        }

        $sectionClassName = '\Ideys\Content\Section\Entity\\'.ucfirst($data['type']);
        $section = new $sectionClassName();

        static::hydrate($section, $data);

        $this->hydrateItems($section);

        if ($hydrateConnectedSections)
            $this->hydrateConnectedSections($section);

        return $section;
    }

    /**
     * Attach Items to their Section.
     *
     * @param Section $section
     */
    protected function hydrateItems(Section $section)
    {
        $items = array();

        $sql = ItemProvider::baseQuery()
            . 'WHERE i.expose_section_id = ? '
            . 'AND t.language = ? '
            . 'ORDER BY i.hierarchy ASC ';
        $rows = $this->db->fetchAll($sql, array($section->getId(), $this->language));

        foreach ($rows as $data) {
            $items[$data['id']] = $this->itemProvider->hydrateItem($data);
        }

        $section->setItems($items);
    }

    /**
     * Insert connected sections items.
     * Currently used by Maps to attach others sections content.
     *
     * @param Section $section
     */
    private function hydrateConnectedSections(Section $section)
    {
        $connectedSectionsId = $section->getConnectedSectionsId();

        if (! empty($connectedSectionsId)) {
            $entities = $this->db
                ->fetchAll(static::baseQuery()
                    . 'WHERE s.id IN  ('.implode(',', $connectedSectionsId).') '
                    . 'GROUP BY s.id '
                    . 'ORDER BY s.hierarchy, i.hierarchy ');

            foreach ($entities as $data) {
                $connectedSection = $this->hydrateSection($data, false);
                $section->addConnectedSection($connectedSection);
            }
        }
    }

    /**
     * Attach an item from another section to this section.
     *
     * @param Section $section
     * @param integer $id The item id
     *
     * @return boolean
     */
    public function attachItem(Section $section, $id)
    {
        $affectedRows = $this->db->update(TABLE_PREFIX.'section_item',
            array('expose_section_id' => $section->getId()),
            array('id' => $id)
        );

        return (boolean) $affectedRows;
    }

    /**
     * Archive or restore a section.
     *
     * @param Section $section
     */
    public function switchArchive(Section $section)
    {
        $this->db->executeQuery(
            'UPDATE '.TABLE_PREFIX.'section ' .
            'SET archive = NOT archive ' .
            'WHERE id = :id ',
            array('id' => $section->getId())
        );
    }

    /**
     * Create or update a section into database.
     *
     * @param Section $section
     */
    public function persist(Section $section)
    {
        if ($section->getId() > 0) {
            $this->update($section);
        } else {
            $this->create($section);
        }
    }

    /**
     * @param Section $section
     *
     * @return Section
     */
    private function create(Section $section) {

        $count = $this->db->fetchAssoc('SELECT COUNT(s.id) AS total FROM '.TABLE_PREFIX.'section AS s');
        $section->setHierarchy(++$count['total']);

        $section->setLanguage($this->language);

        $this->uniqueSlug($section);
        $this->blameAndTimestamp($section);

        $sectionData = $this->objectToArray('section', $section);

        $this->db->insert(TABLE_PREFIX.'section', $sectionData);
        $section->setId($this->db->lastInsertId());

        $translationData = $this->objectToArray('section_trans', $section);
        $translationData['expose_section_id'] = $section->getId();

        $this->db->insert(TABLE_PREFIX.'section_trans', $translationData);
    }

    /**
     * @param Section $section
     */
    public function update(Section $section)
    {
        // Reset old homepage visibility in case of section
        // was newly defined as the homepage.
        // Also remove section from sub-folder.
        if ($section->isHomepage()) {
            $this->db->update(TABLE_PREFIX.'section',
                array('visibility' => Section::VISIBILITY_CLOSED),
                array('visibility' => Section::VISIBILITY_HOMEPAGE)
            );
            $section->setExposeSectionId(null);
        }

        $this->uniqueSlug($section);
        $this->blameAndTimestamp($section);

        $sectionData = $this->objectToArray('section', $section);

        $this->db->update(TABLE_PREFIX.'section',
            $sectionData,
            array('id' => $section->getId()));

        $translationData = $this->objectToArray('section_trans', $section);
        $translationData['expose_section_id'] = $section->getId();

        $this->db->update(TABLE_PREFIX.'section_trans',
            $translationData,
            array(
                'expose_section_id' => $section->getId(),
                'language' => $section->getLanguage(),
            ));
    }

    /**
     * Increments slugs for identical name sections:
     * new-section / new-section-2 / new-section-4 => new-section-5
     *
     * @param Section $section
     */
    private function uniqueSlug(Section $section)
    {
        $title = $section->getTitle();

        // Add a "-dir" suffix to dir sections.
        if ($section->getType() === Section::SECTION_DIR) {
            $title .= '-dir';
        }

        $slug = StringHelper::slugify($title);

        $sections = $this->db->fetchAll(
            'SELECT slug FROM '.TABLE_PREFIX.'section '.
            'WHERE slug LIKE ? '.
            'AND id != ?',
            array($slug.'%', $section->getId())
        );

        $namesakes = array();
        foreach($sections as $namesakeSection) {
            $e = explode('-', $namesakeSection['slug']);
            $prefix = array_pop($e);
            $namesakes[] = (int)$prefix;
        }

        if (!empty($namesakes)) {
            sort($namesakes);
            $lastRow = array_pop($namesakes);
            $slug .= '-' . (++$lastRow);
        }

        $section->setSlug($slug);
    }

    /**
     * Delete a Section and this items in database.
     *
     * @param Section $section
     *
     * @return boolean
     */
    public function delete(Section $section)
    {
        // Delete section items
        foreach ($section->getItems() as $item) {
            $this->itemProvider->delete($item);
        }

        // Delete section's translations
        $this->db->delete(TABLE_PREFIX.'section_trans', array('expose_section_id' => $section->getId()));
        // Delete section
        $rows = $this->db->delete(TABLE_PREFIX.'section', array('id' => $section->getId()));

        return (0 < $rows);
    }

    /**
     * Return SQL statement to extract a Section.
     *
     * @return string
     */
    public static function baseQuery()
    {
        return
            'SELECT s.id, s.expose_section_id, s.connected_sections_id, '.
            's.type, s.slug, s.custom_css, s.custom_js, '.
            's.menu_pos, s.tag, s.visibility, s.shuffle, '.
            's.hierarchy, s.archive, s.target_blank, '.
            't.title, t.description, t.legend, '.
            't.parameters, t.language '.
            'FROM '.TABLE_PREFIX.'section AS s '.
            'LEFT JOIN '.TABLE_PREFIX.'section_trans AS t '.
            'ON t.expose_section_id = s.id '.
            'LEFT JOIN '.TABLE_PREFIX.'section_item AS i '.
            'ON i.expose_section_id = s.id ';
    }
}
