<?php

namespace Ideys\Content\Section\Provider;

use Ideys\Content\AbstractProvider;
use Ideys\Content\Section\Entity;
use Ideys\Content\Item\Provider\ItemProvider;
use Ideys\Content\Item;

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
            if ($section instanceof Entity\Section) {
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
     * @return Entity\Section
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
     * @return Entity\Section
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
     * @return Entity\Section
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
     * @param Entity\Section $section
     */
    public function hydrateItems(Entity\Section $section)
    {
        $items = array();

        $sql = ItemProvider::baseQuery()
            . 'WHERE i.expose_section_id = ? '
            . 'AND t.language = ? '
            . 'ORDER BY i.hierarchy ASC ';
        $rows = $this->db->fetchAll($sql, array($section->getId(), $this->language));

        foreach ($rows as $data) {
            $items[$data['id']] = ItemProvider::hydrateItem($data);
        }

        $section->setItems($items);
    }

    /**
     * Archive or restore a section.
     *
     * @param Entity\Section $section
     */
    public function switchArchive(Entity\Section $section)
    {
        $this->db->executeQuery(
            'UPDATE expose_section ' .
            'SET archive = NOT archive ' .
            'WHERE id = :id ',
            array('id' => $section->getId())
        );
    }

    /**
     * Delete a Section and this items in database.
     *
     * @param Entity\Section $section
     *
     * @return boolean
     */
    public function delete(Entity\Section $section)
    {
        // Delete section items
        foreach ($section->getItems() as $item) {
            if ($item instanceof Item\Entity\Slide) {
                $this->deleteItemAndRelatedFile($item);
            } else {
                $this->deleteItem($item->id);
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
