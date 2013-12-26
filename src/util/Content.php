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

    /**
     * @var string
     */
    private $sqlSelectSection =
       'SELECT s.*, t.title, t.description
        FROM expose_section AS s
        LEFT JOIN expose_section_trans AS t
        ON t.expose_section_id = s.id ';

    /**
     * @var string
     */
    private $sqlSelectItem =
       'SELECT i.*, t.title, t.description, t.content, t.parameters
        FROM expose_section_item AS i
        LEFT JOIN expose_section_item_trans AS t
        ON t.expose_section_item_id = i.id ';

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

        $sql = $this->sqlSelectSection .
           'WHERE t.language = ?
            ORDER BY s.hierarchy ASC';
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

        $sql = $this->sqlSelectItem .
           'WHERE t.language = ?
            ORDER BY i.hierarchy ASC';
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
        $sql = $this->sqlSelectSection .
           'WHERE s.slug = ?
            AND t.language = ?
            ORDER BY s.hierarchy ASC';
        $section = $this->db->fetchAssoc($sql, array($slug, $this->language));

        return $section;
    }

    /**
     * Return a section.
     *
     * @return array
     */
    public function findHomepage()
    {
        $sql = $this->sqlSelectSection .
           'WHERE s.homepage = 1
            AND t.language = ?
            ORDER BY s.hierarchy ASC';
        $section = $this->db->fetchAssoc($sql, array($this->language));

        // Generate default homepage
        if (false === $section) {
            $settings = new Settings($this->db);
            $sectionId = $this->addSection(
                self::CONTENT_PAGE,
                $settings->name,
                '',
                null,
                $this->language
            );
            $this->addItem(
                $sectionId,
                self::CONTENT_PAGE,
                null,
                $settings->name,
                '',
                '<div id="homepage"><h1>'.$settings->name.'</h1></div>',
                array(),
                $this->language
            );
            $this->defindHomepage($sectionId);
            $section = $this->findHomepage();
        }

        return $section;
    }

    /**
     * Define the homepage section.
     *
     * @param integer $sectionId
     */
    public function defindHomepage($sectionId)
    {
        // Reset old homepage
        $this->db->update(
            'expose_section',
            array('homepage' => 0, 'active' => 0),
            array('homepage' => 1)
        );
        $this->db->update(
            'expose_section',
            array('homepage' => 1, 'active' => 1),
            array('id' => $sectionId)
        );
    }

    /**
     * Return a section.
     *
     * @param integer $id Section id
     * @return array
     */
    public function findSectionItems($id)
    {
        $sql = $this->sqlSelectItem .
           'WHERE i.expose_section_id = ?
            AND t.language = ?
            ORDER BY i.hierarchy ASC';
        $items = $this->db->fetchAll($sql, array($id, $this->language));

        foreach ($items as $row => $item) {
            $items[$row]['parameters'] = unserialize($item['parameters']);
        }

        return $items;
    }

    /**
     * Create a new section.
     *
     * @return integer Section id
     */
    public function addSection($type, $title, $description, $dirId, $language, $active = false)
    {
        $dirId = is_numeric($dirId) ? (int)$dirId : null;
        $this->db->insert('expose_section', array(
            'expose_section_id' => $dirId,
            'type' => $type,
            'slug' => slugify($title),
            'active' => $active,
        ) + $this->blameAndTimestampData(0));

        $sectionId = $this->db->lastInsertId();
        $this->db->insert('expose_section_trans', array(
            'expose_section_id' => $sectionId,
            'title' => $title,
            'description' => $description,
            'language' => $language,
        ));

        return $sectionId;
    }

    /**
     * Insert a new content.
     *
     * @return integer Item id
     */
    public function addItem($sectionId, $type, $path, $title, $description, $content, $settings, $language)
    {
        $sectionId = is_numeric($sectionId) ? (int)$sectionId : null;
        $this->db->insert('expose_section_item', array(
            'expose_section_id' => $sectionId,
            'type' => $type,
            'slug' => slugify($title),
            'path' => $path,
        ) + $this->blameAndTimestampData(0));

        $itemId = $this->db->lastInsertId();
        $this->db->insert('expose_section_item_trans', array(
            'expose_section_item_id' => $itemId,
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'parameters' => serialize($settings),
            'language' => $language,
        ));

        return $itemId;
    }

    /**
     * Update a content.
     *
     * @return integer Item id
     */
    public function editItem($entity)
    {
        $this->db->update(
            'expose_section_item',
            array(
                'path' => $entity['path'],
            ) + $this->blameAndTimestampData($entity['id']),
            array('id' => $entity['id'])
        );
        $this->db->update(
            'expose_section_item_trans',
            array(
                'title' => $entity['title'],
                'description' => $entity['description'],
                'content' => $entity['content'],
            ),
            array(
                'expose_section_item_id' => $entity['id'],
                'language' => $this->language,
            )
        );
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
    private function blameAndTimestampData($id)
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
