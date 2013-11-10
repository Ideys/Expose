<?php

use Doctrine\DBAL\Connection;

/**
 * App content manager.
 */
class Content
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * @var string
     */
    private $language;

    /**
     * @var array
     */
    private $sections = array();


    /**
     * Constructor: inject required Silex dependencies.
     *
     * @param array $app
     */
    public function __construct(Connection $connection)
    {
        $this->db = $connection;
        $this->language = 'fr';
    }

    /**
     * Return sections.
     *
     * @return array
     */
    public function findSections()
    {
        if (!empty($this->sections)) {
            return $this->sections;
        }

        $sql = "SELECT s.*, t.title, t.description
                FROM expose_section AS s
                LEFT JOIN expose_section_trans AS t
                ON t.expose_section_id = s.id
                WHERE t.language = ?
                ORDER BY s.hierarchy ASC";
        $sections = $this->db->fetchAll($sql, array($this->language));

        // Use sql primary keys as array keys and add sections tree
        foreach ($sections as $section) {
            $this->sections[$section['id']] = $section + array('sections' => array());
        }

        // Generate tree structure from raw datas
        foreach ($this->sections as $id => $section) {
            $parentSectionId = $section['expose_section_id'];
            if ($parentSectionId > 0) {
                $this->sections[$parentSectionId]['sections'][] = $section;
                unset($this->sections[$id]);
            }
        }

        return $this->sections;
    }

    /**
     * Return a section.
     *
     * @return array
     */
    public function findSection($slug)
    {
        $sql = "SELECT s.*, t.title, t.description
                FROM expose_section AS s
                LEFT JOIN expose_section_trans AS t
                ON t.expose_section_id = s.id
                WHERE s.slug = ?
                AND t.language = ?
                ORDER BY s.hierarchy ASC";
        $section = $this->db->fetchAssoc($sql, array($slug, $this->language));

        return $section;
    }

    /**
     * Create a new section.
     */
    public function addSection($type, $title, $description, $language, $active = false)
    {
        $this->db->insert('expose_section', array(
            'type' => $type,
            'slug' => slugify($title),
            'active' => $active,
        ));
        $sectionId = $this->db->lastInsertId();
        $this->db->insert('expose_section_trans', array(
            'expose_section_id' => $sectionId,
            'title' => $title,
            'description' => $description,
            'language' => $language,
        ));
    }
}
