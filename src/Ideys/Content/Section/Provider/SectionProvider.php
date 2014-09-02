<?php

namespace Ideys\Content\Section\Provider;

use Ideys\Content\AbstractProvider;
use Ideys\Content\Section\Entity\Section;
use Ideys\Content\Item\Provider\ItemProvider;
use Ideys\Content\Item\Entity\Slide;
use Ideys\String;

/**
 * Section provider global class.
 */
class SectionProvider extends AbstractProvider
{
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
            . 'GROUP BY s.id '
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
     * Instantiate a related content object from database entity.
     *
     * @param array $data
     *
     * @return Section
     */
    public function hydrateSection(array $data)
    {
        $sectionClassName = '\Ideys\Content\Section\Entity\\'.ucfirst($data['type']);
        $section = new $sectionClassName();

        static::hydrate($section, $data);

        $this->hydrateItems($section);

        return $section;
    }

    /**
     * Attach Items to their Section.
     *
     * @param Section $section
     */
    public function hydrateItems(Section $section)
    {
        $items = array();

        $sql = ItemProvider::baseQuery()
            . 'WHERE i.expose_section_id = ? '
            . 'AND t.language = ? '
            . 'ORDER BY i.hierarchy ASC ';
        $rows = $this->db->fetchAll($sql, array($section->getId(), $this->language));

        $itemProvider = new ItemProvider($this->db, $this->security);
        foreach ($rows as $data) {
            $items[$data['id']] = $itemProvider->hydrateItem($data);
        }

        $section->setItems($items);
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
        $affectedRows = $this->db->update('expose_section_item',
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
            'UPDATE expose_section ' .
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

        $count = $this->db->fetchAssoc(
            'SELECT COUNT(s.id) AS total '.
            'FROM expose_section AS s');
        $section->setHierarchy(++$count['total']);

        $section->setLanguage($this->language);

        $this->uniqueSlug($section);
        $this->blameAndTimestamp($section);

        $sectionData = $this->objectToArray('expose_section', $section);

        $this->db->insert('expose_section', $sectionData);
        $section->setId($this->db->lastInsertId());

        $translationData = $this->objectToArray('expose_section_trans', $section);
        $translationData['expose_section_id'] = $section->getId();

        $this->db->insert('expose_section_trans', $translationData);
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
            $this->db->update('expose_section',
                array('visibility' => Section::VISIBILITY_CLOSED),
                array('visibility' => Section::VISIBILITY_HOMEPAGE)
            );
            $section->setExposeSectionId(null);
        }

        $this->uniqueSlug($section);
        $this->blameAndTimestamp($section);

        $sectionData = $this->objectToArray('expose_section', $section);

        $this->db->update('expose_section',
            $sectionData,
            array('id' => $section->getId()));

        $translationData = $this->objectToArray('expose_section_trans', $section);
        $translationData['expose_section_id'] = $section->getId();

        $this->db->update('expose_section_trans',
            $translationData,
            array(
                'expose_section_id' => $section->getId(),
                'language' => $section->getLanguage(),
            ));

        // Update other sections parameters with identical tag
        if ($section->getTag() != null) {
//            $this->updateGroupedSections($section);
        }
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

        $slug = String::slugify($title);

        $sections = $this->db->fetchAll(
            'SELECT slug FROM expose_section '.
            'WHERE slug LIKE ? '.
            'AND id != ?',
            array($slug.'%', $section->getId())
        );

        $namesakes = array();
        foreach($sections as $section) {
            $e = explode('-', $section['slug']);
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
            if ($item instanceof Slide) {
                $this->deleteItemAndRelatedFile($item);
            } else {
                $this->deleteItem($item->getId());
            }
        }

        // Delete section's translations
        $this->db->delete('expose_section_trans', array('expose_section_id' => $section->getId()));
        // Delete section
        $rows = $this->db->delete('expose_section', array('id' => $section->getId()));

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
            'SELECT s.id, s.expose_section_id, s.connected_sections, '.
            's.type, s.slug, s.custom_css, s.custom_js, '.
            's.menu_pos, s.tag, s.visibility, s.shuffle, '.
            's.hierarchy, s.archive, s.target_blank, '.
            't.title, t.description, t.legend, '.
            't.parameters, t.language '.
            'FROM expose_section AS s '.
            'LEFT JOIN expose_section_trans AS t '.
            'ON t.expose_section_id = s.id '.
            'LEFT JOIN expose_section_item AS i '.
            'ON i.expose_section_id = s.id ';
    }
}
