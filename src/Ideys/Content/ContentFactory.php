<?php

namespace Ideys\Content;

use Ideys\Content\Section\Section;
use Ideys\Content\Section\Html;
use Ideys\Content\Item;
use Ideys\String;
use Ideys\Settings\Settings;
use Silex\Application;
use Symfony\Component\Security\Core\User\User;
use Doctrine\DBAL\Connection;

/**
 * App content manager.
 */
class ContentFactory
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    protected $security;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var array
     */
    protected $sections = array();

    const SECTION_GALLERY   = 'gallery';
    const SECTION_CHANNEL   = 'channel';
    const SECTION_HTML      = 'html';
    const SECTION_BLOG      = 'blog';
    const SECTION_FORM      = 'form';
    const SECTION_MAPS      = 'maps';
    const SECTION_LINK      = 'link';
    const SECTION_DIR       = 'dir';

    const ITEM_SLIDE        = 'slide';
    const ITEM_VIDEO        = 'video';
    const ITEM_PAGE         = 'page';
    const ITEM_POST         = 'post';
    const ITEM_FIELD        = 'field';
    const ITEM_PLACE        = 'place';

    /**
     * Constructor: inject required Silex dependencies.
     *
     * @param \Silex\Application $app
     */
    public function __construct(Application $app)
    {
        $this->db = $app['db'];
        $this->translator = $app['translator'];
        $this->security = $app['security'];
        $this->language = $this->translator->getLocale();
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

        $sql = $this::getSqlSelectSection()
           . 'WHERE t.language = ? '
           . 'GROUP BY s.id '
           . 'ORDER BY s.hierarchy ASC ';
        $sections = $this->db->fetchAll($sql, array($this->language));

        // Use sql primary keys as array keys and objectify entity
        foreach ($sections as $section) {
            $this->sections[$section['id']] = static::instantiateSection($this->db, $section);
        }

        // Generate tree structure from raw data
        foreach ($this->sections as $id => $section) {
            $parentSectionId = $section->expose_section_id;
            if ($parentSectionId > 0) {
                $this->sections[$parentSectionId]->addSection($section);
                unset($this->sections[$id]);
            }
        }

        return $this->sections;
    }

    /**
     * Return a section.
     *
     * @param integer $id
     *
     * @return \Ideys\Content\Section\Section
     */
    public function findSection($id)
    {
        $sql = $this::getSqlSelectSection()
           . 'WHERE s.id = ? '
           . 'ORDER BY s.hierarchy ASC ';
        $sectionTranslations = $this->db->fetchAll($sql, array($id));

        return $this->hydrateSection($sectionTranslations);
    }

    /**
     * Return a section.
     *
     * @param string $slug Section slug
     *
     * @return \Ideys\Content\Section\Section
     */
    public function findSectionBySlug($slug)
    {
        $sql = $this::getSqlSelectSection()
           . 'WHERE s.slug = ? '
           . 'ORDER BY s.hierarchy ASC ';
        $sectionTranslations = $this->db->fetchAll($sql, array($slug));

        return $this->hydrateSection($sectionTranslations);
    }

    /**
     * Find the homepage section, create it if not exists.
     *
     * @return \Ideys\Content\Section\Section
     */
    public function findHomepage()
    {
        $sql = $this::getSqlSelectSection()
           . 'WHERE s.visibility = ? '
           . 'ORDER BY s.hierarchy ASC ';
        $sectionTranslations = $this->db->fetchAll($sql, array(Section::VISIBILITY_HOMEPAGE));
        $section = $this->hydrateSection($sectionTranslations);

        // Generate default homepage
        if (null === $section->id) {
            $settings = new Settings($this->db);
            $section = $this->addSection(new Html($this->db, array(
                'type' => self::SECTION_HTML,
                'title' => $settings->name,
                'visibility' => Section::VISIBILITY_HOMEPAGE,
            )));
            $page = new Item\Page(array(
                'type' => self::ITEM_PAGE,
                'title' => $settings->name,
                'content' => '<div id="homepage"><h1>'.$settings->name.'</h1></div>',
            ));
            $this->addItem($section, $page);
        }

        return $section;
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
     * Persist a new section.
     *
     * @param \Ideys\Content\Section\Section $section
     *
     * @return \Ideys\Content\Section\Section
     */
    public function addSection(Section &$section)
    {
        $count = $this->db->fetchAssoc('SELECT COUNT(s.id) AS total FROM expose_section AS s');
        $i = $count['total']++;

        $this->db->insert('expose_section', array(
            'expose_section_id' => $section->expose_section_id,
            'type' => $section->type,
            'slug' => $this->uniqueSlug($section),
            'custom_css' => $section->custom_css,
            'custom_js' => $section->custom_js,
            'menu_pos' => $section->menu_pos,
            'target_blank' => $section->target_blank,
            'visibility' => $section->visibility,
            'shuffle' => $section->shuffle,
            'archive' => 0,
            'hierarchy' => $i,
        ) + $this->blameAndTimestampData(0));

        $section->id = $this->db->lastInsertId();
        $this->db->insert('expose_section_trans', array(
            'expose_section_id' => $section->id,
            'title' => $section->title,
            'description' => $section->description,
            'legend' => $section->legend,
            'language' => $this->language,
            'parameters' => serialize($section->parameters),
        ));

        return $section;
    }

    /**
     * Edit a section.
     *
     * @param \Ideys\Content\Section\Section $section
     */
    public function updateSection(Section $section)
    {
        // Reset old homepage visibility in case of section
        // was newly defined as the homepage.
        // Also remove section from sub-folder.
        if (Section::VISIBILITY_HOMEPAGE == $section->visibility) {
            $this->db->update('expose_section',
                array('visibility' => Section::VISIBILITY_CLOSED),
                array('visibility' => Section::VISIBILITY_HOMEPAGE)
            );
            $section->expose_section_id = null;
        }

        // Update section
        $this->db->update('expose_section', array(
            'slug' => $this->uniqueSlug($section),
            'custom_css' => $section->custom_css,
            'custom_js' => $section->custom_js,
            'tag' => $section->tag,
            'menu_pos' => $section->menu_pos,
            'target_blank' => $section->target_blank,
            'visibility' => $section->visibility,
            'shuffle' => $section->shuffle,
            'expose_section_id' => $section->expose_section_id,
        ) + $this->blameAndTimestampData($section->id),
        array('id' => $section->id));

        // Update translated section attributes
        $this->db->update('expose_section_trans', array(
            'title' => $section->title,
            'description' => $section->description,
            'legend' => $section->legend,
            'parameters' => serialize($section->parameters),
        ), array('expose_section_id' => $section->id, 'language' => $this->language));

        // Update other sections parameters with identical tag
        if ($section->tag != null) {
            $this->updateGroupedSections($section);
        }
    }

    /**
     * Update all sections common parameters with identical tag.
     *
     * @param \Ideys\Content\Section\Section $section
     */
    private function updateGroupedSections(Section $section)
    {
        $this->db->update('expose_section', array(
            'custom_css' => $section->custom_css,
            'custom_js' => $section->custom_js,
            'shuffle' => $section->shuffle,
        ),
        array('tag' => $section->tag, 'type' => $section->type));

        // Update translated sections parameters
        $sectionsIds = $this->db->fetchAll(
            'SELECT id FROM expose_section WHERE tag = ? AND type = ?',
            array($section->tag, $section->type)
        );

        foreach ($sectionsIds as $id) {
            $this->db->update('expose_section_trans', array(
                'parameters' => serialize($section->parameters),
            ), array('expose_section_id' => $id['id'], 'language' => $this->language));
        }
    }

    /**
     * Increments slugs for identical name sections:
     * new-section / new-section-2 / new-section-4 => new-section-5
     *
     * @param \Ideys\Content\Section\Section $section
     *
     * @return string
     */
    protected function uniqueSlug(Section $section)
    {
        $title = $section->title;

        // Add a "-dir" suffix to dir sections.
        if ($section->type === self::SECTION_DIR) {
            $title .= '-dir';
        }

        $slug = String::slugify($title);

        $sections = $this->db->fetchAll(
            'SELECT slug FROM expose_section WHERE slug LIKE ? AND id != ?',
            array($slug.'%', $section->id)
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

        return $slug;
    }

    /**
     * Return an Item.
     *
     * @param integer $id
     *
     * @return \Ideys\Content\Item\Item
     */
    public function findItem($id)
    {
        $sql = $this::getSqlSelectItem()
            . 'WHERE i.id = ? '
            . 'ORDER BY s.hierarchy ASC ';
        $data = $this->db->fetchAssoc($sql, array($id));

        return static::instantiateItem($data);
    }

    /**
     * Insert a new content.
     *
     * @param \Ideys\Content\Section\Section    $section
     * @param \Ideys\Content\Item\Item          $item
     *
     * @return \Ideys\Content\Item\Item $item
     */
    public function addItem(Section $section, Item\Item $item)
    {
        $this->db->insert('expose_section_item', array(
            'expose_section_id' => $section->id,
            'type' => $item->type,
            'category' => $item->category,
            'slug' => String::slugify($item->title),
            'path' => $item->path,
            'posting_date' => static::dateToDatabase($item->posting_date),
            'author' => $item->author,
            'published' => $item->published,
            'hierarchy' => $item->hierarchy,
        ) + $this->blameAndTimestampData(0));

        $item->id = $this->db->lastInsertId();
        $this->db->insert('expose_section_item_trans', array(
            'expose_section_item_id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'content' => $item->content,
            'parameters' => serialize($item->parameters),
            'language' => $this->language,
        ));

        return $item;
    }

    /**
     * Update a content.
     *
     * @param \Ideys\Content\Item\Item $item
     *
     * @return \Ideys\Content\Item\Item
     */
    public function editItem(Item\Item $item)
    {
        $this->db->update(
            'expose_section_item',
            array(
                'path' => $item->path,
                'posting_date' => static::dateToDatabase($item->posting_date),
                'author' => $item->author,
            ) + $this->blameAndTimestampData($item->id),
            array('id' => $item->id)
        );
        $this->db->update(
            'expose_section_item_trans',
            array(
                'title' => $item->title,
                'description' => $item->description,
                'parameters' => serialize($item->parameters),
                'content' => $item->content,
            ),
            array(
                'expose_section_item_id' => $item->id,
                'language' => $this->language,
            )
        );

        return $item;
    }

    /**
     * Update item title and description.
     *
     * @param integer $id
     * @param string  $title
     * @param string  $description
     * @param string  $link
     */
    public function updateItemTitle($id, $title, $description, $link)
    {
        $this->db->update(
            'expose_section_item_trans',
            array(
                'title' => $title,
                'description' => $description,
                'link' => $link,
            ),
            array(
                'expose_section_item_id' => $id,
                'language' => $this->language,
            )
        );
    }

    /**
     * Return section types keys.
     *
     * @return array
     */
    public static function getSectionTypes()
    {
        return array(
            self::SECTION_GALLERY,
            self::SECTION_CHANNEL,
            self::SECTION_HTML,
            self::SECTION_BLOG,
            self::SECTION_FORM,
            self::SECTION_MAPS,
            self::SECTION_LINK,
            self::SECTION_DIR,
        );
    }

    /**
     * Return item types keys.
     *
     * @return array
     */
    public static function getItemTypes()
    {
        return array(
            self::ITEM_SLIDE,
            self::ITEM_VIDEO,
            self::ITEM_PAGE,
            self::ITEM_POST,
            self::ITEM_FIELD,
            self::ITEM_PLACE,
        );
    }

    /**
     * Return instantiated Section from array data.
     *
     * @param \Doctrine\DBAL\Connection $db
     * @param array                     $data
     *
     * @return Section
     */
    public static function instantiateSection(Connection $db, $data)
    {
        $type = $data['type'];

        if (!in_array($type, static::getSectionTypes())) {
            $type = self::SECTION_HTML;
        }

        $sectionClass = '\Ideys\Content\Section\\'.ucfirst($type);

        return new $sectionClass($db, $data);
    }

    /**
     * Return instantiated Item from array data.
     *
     * @param array     $data
     *
     * @return \Ideys\Content\Item\Item
     */
    public static function instantiateItem($data)
    {
        if (!in_array($data['type'], static::getItemTypes())) {
            $data['type'] = static::getDefaultSectionItemType($data['section_type']);
        }

        $itemClass = '\Ideys\Content\Item\\'.ucfirst($data['type']);
        return new $itemClass($data);
    }

    /**
     * Return item types keys.
     *
     * @param  string $type The section type
     *
     * @return string       The section default item
     */
    public static function getDefaultSectionItemType($type)
    {
        $sectionTypes = static::getSectionTypes();
        $sectionTypes = array_diff($sectionTypes, array(self::SECTION_LINK, self::SECTION_DIR));
        $itemTypes = static::getItemTypes();
        $sectionItems = array_combine($sectionTypes, $itemTypes);

        return $sectionItems[$type];
    }

    /**
     * Instantiate a related content object from database entity.
     *
     * @param array $sectionTranslations
     *
     * @return Section
     */
    private function hydrateSection(array $sectionTranslations)
    {
        if (empty($sectionTranslations)) {
            return false;
        }

        $sectionData = $this->retrieveLanguage($sectionTranslations, $this->language);
        $section = static::instantiateSection($this->db, $sectionData);
        $section->hydrateItems();
        $section->setLanguage($this->language);

        return $section;
    }

    /**
     * Retrieve section in current language or fallback to default one.
     *
     * @param array  $translations
     * @param string $language
     *
     * @return string
     */
    private function retrieveLanguage(array $translations, $language)
    {
        foreach ($translations as $translation) {
            if ($translation['language'] == $language) {
                return $translation;
            }
        }

        return $translations[0];
    }

    /**
     * Return SQL statement to extract a Section.
     *
     * @return string
     */
    public static function getSqlSelectSection()
    {
        return
        'SELECT s.id, s.expose_section_id, '.
               's.type, s.slug, s.custom_css, s.custom_js, '.
               's.menu_pos, s.tag, s.visibility, s.shuffle, '.
               's.hierarchy, s.archive, s.target_blank, '.
               't.title, t.description, t.legend, '.
               't.parameters, t.language, '.
               'COUNT(i.id) AS total_items '.
        'FROM expose_section AS s '.
        'LEFT JOIN expose_section_trans AS t '.
        'ON t.expose_section_id = s.id '.
        'LEFT JOIN expose_section_item AS i '.
        'ON i.expose_section_id = s.id '.
        'AND ( '.
        '(i.type = \'Post\' AND s.type = \'Blog\') '.
        'OR  (i.type = \'Video\' AND s.type = \'Channel\') '.
        'OR  (i.type = \'Field\' AND s.type = \'Form\') '.
        'OR  (i.type = \'Slide\' AND s.type = \'Gallery\') '.
        'OR  (i.type = \'Page\' AND s.type = \'Html\') '.
        'OR  (i.type = \'Place\' AND s.type = \'Maps\') '.
        ') ';
    }

    /**
     * Return SQL statement to extract an Item.
     *
     * @return string
     */
    public static function getSqlSelectItem()
    {
        return
        'SELECT i.*, t.title, t.description, t.content, '.
               't.link, t.parameters, t.language, s.type AS section_type '.
        'FROM expose_section_item AS i '.
        'LEFT JOIN expose_section_item_trans AS t '.
        'ON t.expose_section_item_id = i.id '.
        'LEFT JOIN expose_section AS s '.
        'ON i.expose_section_id = s.id ';
    }

    /**
     * Format a datetime to be persisted.
     *
     * @param \DateTime $datetime
     *
     * @return null|string
     */
    private static function dateToDatabase(\DateTime $datetime = null)
    {
        return ($datetime instanceof \DateTime)
            ? $datetime->format('c') : null;
    }

    /**
     * Define user author and timestamp for persisted data.
     *
     * @param integer $id
     *
     * @return array
     */
    private function blameAndTimestampData($id)
    {
        $securityToken = $this->security->getToken();
        $datetime = (new \DateTime())->format('c');
        $userId = null;

        if (!empty($securityToken)) {
            $loggedUser = $securityToken->getUser();
            if ($loggedUser instanceof User) {
                $user = $this->db
                        ->fetchAssoc('SELECT id FROM expose_user WHERE username = ?', array(
                    $loggedUser->getUsername(),
                ));
                $userId = $user['id'];
            }
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
