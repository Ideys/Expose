<?php

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\SecurityContext;

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
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    private $security;

    /**
     * @var string
     */
    private $language;

    /**
     * @var array
     */
    private $sections = array();

    /**
     * @var array
     */
    private $items = array();

    const CONTENT_GALLERY   = 'gallery';
    const CONTENT_VIDEO     = 'video';
    const CONTENT_PAGE      = 'page';
    const CONTENT_FORM      = 'form';
    const CONTENT_DIR       = 'dir';

    /**
     * Constructor: inject required Silex dependencies.
     *
     * @param \Doctrine\DBAL\Connection $connection
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
     * Return all items.
     *
     * @return array
     */
    public function findItems()
    {
        if (!empty($this->items)) {
            return $this->items;
        }

        $sql = "SELECT i.*, t.title, t.description, t.content
                FROM expose_section_item AS i
                LEFT JOIN expose_section_item_trans AS t
                ON t.expose_section_item_id = i.id
                WHERE t.language = ?
                ORDER BY i.hierarchy ASC";
        $items = $this->db->fetchAll($sql, array($this->language));

        //Group by section ids
        foreach ($items as $item) {
            $this->items[(int)$item['expose_section_id']][] = $item;
        }

        return $this->items;
    }

    /**
     * Return a section.
     *
     * @param string $slug Section slug
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
     * Return a section.
     *
     * @param integer $id Section id
     * @return array
     */
    public function findSectionItems($id)
    {
        $sql = "SELECT i.*, t.title, t.description, t.content
                FROM expose_section_item AS i
                LEFT JOIN expose_section_item_trans AS t
                ON t.expose_section_item_id = i.id
                WHERE i.expose_section_id = ?
                AND t.language = ?
                ORDER BY i.hierarchy ASC";
        $items = $this->db->fetchAll($sql, array($id, $this->language));

        return $items;
    }

    /**
     * Create a new section.
     */
    public function addSection($type, $title, $description, $dirId, $language, $active = false)
    {
        $dirId = is_numeric($dirId) ? (int)$dirId : null;
        $this->db->insert('expose_section', array(
            'expose_section_id' => $dirId,
            'type' => $type,
            'slug' => slugify($title),
            'active' => $active,
        ) + $this->blameAndTimestampData());

        $sectionId = $this->db->lastInsertId();
        $this->db->insert('expose_section_trans', array(
            'expose_section_id' => $sectionId,
            'title' => $title,
            'description' => $description,
            'language' => $language,
        ));
    }

    /**
     * Insert a new content.
     */
    public function addItem($dirId, $type, $path, $title, $description, $content, $language)
    {
        $dirId = is_numeric($dirId) ? (int)$dirId : null;
        $this->db->insert('expose_section_item', array(
            'expose_section_id' => $dirId,
            'type' => $type,
            'slug' => slugify($title),
            'path' => $path,
        ) + $this->blameAndTimestampData());

        $itemId = $this->db->lastInsertId();
        $this->db->insert('expose_section_item_trans', array(
            'expose_section_item_id' => $itemId,
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'language' => $language,
        ));
    }

    /**
     * Return content types keys
     *
     * @return array
     */
    public static function getContentTypes()
    {
        return array(
            self::CONTENT_GALLERY,
            self::CONTENT_VIDEO,
            self::CONTENT_PAGE,
            self::CONTENT_FORM,
            self::CONTENT_DIR,
        );
    }

    /**
     * Return content types keys and trans values
     * Used on select forms.
     *
     * @return array
     */
    public static function getContentTypesChoice()
    {
        $keys = static::getContentTypes();
        $values = array_map(function($item){
            return 'content.'.$item;
        }, $keys);
        return array_combine($keys, $values);
    }

    /**
     * Define user id to blame next persisted data.
     *
     * @param \Symfony\Component\Security\Core\SecurityContext  $security
     * @return Content
     */
    public function blame(SecurityContext $security)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * Define user author and timestamp for persisted data.
     *
     * @param integer $id
     * @return array
     */
    private function blameAndTimestampData($id = 0)
    {
        $datetime = (new \DateTime())->format('c');
        if ($this->security instanceof SecurityContext) {
            $loggedUser = $this->security->getToken()->getUser();
            $user = $this->db->fetchAssoc('SELECT id FROM expose_user WHERE username = ?', array(
                $loggedUser->getUsername(),
            ));
            $userId = $user['id'];
        } else {
            $userId = null;
        }

        return array(
            'updated_by' => $userId,
            'updated_at' => $datetime,
        ) + (($id == 0) ? array(
            'created_by' => $userId,
            'created_at' => $datetime,
        ) : array());
    }
}
