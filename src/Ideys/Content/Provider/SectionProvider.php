<?php

namespace Ideys\Content\Provider;

use Doctrine\DBAL\Connection;
use Ideys\Content\ContentHydrator;
use Ideys\Content\Item;

/**
 * Section provider global class.
 */
class SectionProvider
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

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
     * Return a section.
     *
     * @param integer $id
     *
     * @return \Ideys\Content\Section\Section
     */
    public function find($id)
    {
        $sql = static::baseQuery()
            . 'WHERE s.id = ? '
            . 'ORDER BY s.hierarchy ASC ';
        $sectionTranslations = $this->db->fetchAll($sql, array($id));

        $contentHydrator = new ContentHydrator();
        return $contentHydrator->hydrateSection($sectionTranslations, 'fr');
    }

    /**
     * Return a section.
     *
     * @param string $slug Section slug
     *
     * @return \Ideys\Content\Section\Section
     */
    public function findBySlug($slug)
    {
        $sql = static::baseQuery()
            . 'WHERE s.slug = ? '
            . 'ORDER BY s.hierarchy ASC ';
        $sectionTranslations = $this->db->fetchAll($sql, array($slug));

        $contentHydrator = new ContentHydrator();
        return $contentHydrator->hydrateSection($sectionTranslations, 'fr');
    }

    /**
     * Archive or restore a section.
     *
     * @param integer $sectionId
     */
    public function switchArchive($sectionId)
    {
        $this->db->executeQuery('UPDATE expose_section SET archive = NOT archive '
            . 'WHERE id = :id',
            array('id' => $sectionId)
        );
    }

    /**
     * Delete a Section and this items in database.
     *
     * @return boolean
     */
    public function delete()
    {
        // Delete section items
        foreach ($this->items as $item) {
            if ($item instanceof Item\Slide) {
                $this->deleteItemAndRelatedFile($item);
            } else {
                $this->deleteItem($item->id);
            }
        }

        // Delete section's translations
        $this->db->delete('expose_section_trans', array('expose_section_id' => $this->id));
        // Delete section
        $rows = $this->db->delete('expose_section', array('id' => $this->id));

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
