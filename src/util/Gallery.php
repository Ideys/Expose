<?php

/**
 * App gallery manager.
 */
class Gallery
{
    /**
     * @var Doctrine
     */
    private $orm;

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
    public function __construct($app)
    {
        $this->orm = $app['db'];
        $this->language = 'fr';
    }

    /**
     * Return gallery sections.
     *
     * @return array
     */
    public function findSections()
    {
        if (!empty($this->sections)) {
            return $this->sections;
        }

        $sql = "SELECT s.*, t.title, t.description, t.credits
                FROM expose_section AS s
                LEFT JOIN expose_section_trans AS t
                ON t.expose_section_id = s.id
                WHERE t.language = ?
                ORDER BY s.hierarchy ASC";
        $sections = $this->orm->fetchAll($sql, array($this->language));

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
     * Return a gallery section.
     *
     * @return array
     */
    public function findSection($slug)
    {
        $sql = "SELECT s.*, t.title, t.description, t.credits
                FROM expose_section AS s
                LEFT JOIN expose_section_trans AS t
                ON t.expose_section_id = s.id
                WHERE s.slug = ?
                AND t.language = ?
                ORDER BY s.hierarchy ASC";
        $section = $this->orm->fetchAssoc($sql, array($slug, $this->language));

        return $section;
    }
}
