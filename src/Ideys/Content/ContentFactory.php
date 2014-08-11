<?php

namespace Ideys\Content;

use Ideys\Content\Provider\ItemProvider;
use Ideys\Content\Provider\SectionProvider;
use Ideys\Content\Section;
use Ideys\Content\Item;
use Ideys\String;
use Ideys\Settings\Settings;
use Silex\Application;
use Symfony\Component\Security\Core\User\User;
use Doctrine\Common\Inflector\Inflector;

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

        $sql = SectionProvider::baseQuery()
           . 'WHERE t.language = ? '
           . 'GROUP BY s.id '
           . 'ORDER BY s.hierarchy ASC ';
        $sections = $this->db->fetchAll($sql, array($this->language));

        // Use sql primary keys as array keys and objectify entity
        foreach ($sections as $section) {
            $this->sections[$section['id']] = ContentHydrator::instantiateSection($section);
        }

        // Generate tree structure from raw data
        foreach ($this->sections as $id => $section) {
            $parentSectionId = $section->getExposeSectionId();
            if ($parentSectionId > 0) {
                $this->sections[$parentSectionId]->addSection($section);
                unset($this->sections[$id]);
            }
        }

        return $this->sections;
    }

    /**
     * Return all linkable sections to a Map section.
     * Exclude other Map sections and Dir sections.
     *
     * @return array
     */
    public function getLinkableSections()
    {
        return  $this->db
            ->fetchAll(
                'SELECT s.id, s.expose_section_id, '.
                's.type, s.slug, s.visibility, '.
                't.title, t.description, t.legend, t.parameters '.
                'FROM expose_section AS s '.
                'LEFT JOIN expose_section_trans AS t '.
                'ON t.expose_section_id = s.id '.
                'WHERE s.type NOT IN  (\'dir\', \'map\') '.
                'AND s.archive = 0 '.
                'ORDER BY s.hierarchy ');
    }

    /**
     * Return linked Sections Items.
     *
     * @param Section\Section $section
     *
     * @return array
     */
    public function getLinkedSectionsItems(Section\Section $section)
    {
        $linkedItems = array();

        if (empty($this->connectedSectionsId)) {
            $entities = array();
        } else {
            $entities = $this->db
                ->fetchAll(
                    ItemProvider::baseQuery().
                    'WHERE s.id IN  ('.implode(',', $section->getConnectedSectionsId()).') '.
                    'ORDER BY s.hierarchy, i.hierarchy ');
        }

        foreach ($entities as $data) {
            $linkedItems[$data['id']] = ContentFactory::instantiateItem($data);
        }

        return $linkedItems;
    }

    /**
     * Return the first viewable section.
     *
     * @return \Ideys\Content\Section\Section
     */
    public function findFirstSection()
    {
        $sql = SectionProvider::baseQuery()
            . "WHERE s.type NOT IN ('link', 'dir')"
            . "AND s.visibility NOT IN ('homepage', 'closed') ";
        $sectionTranslations = $this->db->fetchAll($sql);

        $contentHydrator = new ContentHydrator();

        return $contentHydrator->hydrateSection($sectionTranslations, $this->language);
    }

    /**
     * Find the homepage section, create it if not exists.
     *
     * @return \Ideys\Content\Section\Section
     */
    public function findHomepage()
    {
        $sql = SectionProvider::baseQuery()
           . 'WHERE s.visibility = ? '
           . 'ORDER BY s.hierarchy ASC ';
        $sectionTranslations = $this->db->fetchAll($sql, array(Section\Section::VISIBILITY_HOMEPAGE));

        $contentHydrator = new ContentHydrator();
        $section = $contentHydrator->hydrateSection($sectionTranslations, $this->language);

        // Generate default homepage
        if (null === $section->getId()) {
            $settings = new Settings($this->db);
            $section = $this->addSection(new Section\Html($this->db, array(
                'type' => Section\Section::SECTION_HTML,
                'title' => $settings->getName(),
                'visibility' => Section\Section::VISIBILITY_HOMEPAGE,
            )));
            $page = new Item\Page(array(
                'type' => Item\Item::ITEM_PAGE,
                'title' => $settings->getName(),
                'content' => '<div id="homepage"><h1>'.$settings->getName().'</h1></div>',
            ));
            $this->addItem($section, $page);
        }

        return $section;
    }

    /**
     * Replace sections keys replacement for composite sections.
     *
     * - Gallery integration
     * - Video integration
     *
     * @param Section\SectionInterface $section
     * @param \Twig_Environment        $twig
     */
    public function composeSectionItems(Section\SectionInterface $section, \Twig_Environment $twig)
    {
        if ($section->isComposite()) {

            $items = $section->getDefaultItems();

            // A: extract replacement keys
            $sectionSlugs = array();
            $galleries = array();
            foreach ($items as $item) {
                if ($item instanceof Item\Item) {
                    $content = $item->getContent();
                    $countMatch = preg_match_all('/__(slides|video):([\w\@-]+)__/', $content, $matches);
                    if ((int)$countMatch > 0) {
                        $keys = $matches[0];
                        $contentType = $matches[1];
                        foreach ($matches[2] as $row => $slug) {
                            $sectionSlugs[$contentType[$row]][$keys[$row]] = $slug;
                        }
                        $galleries = $sectionSlugs['slides'];
                    }
                }
            }

            // B: retrieve related sections
            $replacementValues = array();
            if (!empty($galleries)) {
                $sanitizedSlugs = filter_var_array($galleries, FILTER_SANITIZE_STRING);
                $sql = SectionProvider::baseQuery()
                    . 'WHERE s.slug IN (\''. implode("', '", $sanitizedSlugs) .'\') '
                    . 'AND t.language = ? '
                    . "AND s.type IN ('gallery', 'channel') ";

                $sectionsToInclude = $this->db->fetchAll($sql, array($this->language));

                $replacementStrings = array_flip($galleries);
                foreach ($sectionsToInclude as $s) {
                    $sectionToInclude = static::instantiateSection($s);
                    $sectionToInclude->hydrateItems();
                    if ($sectionToInclude->hasDefaultItems()) {
                        $replacementValues[$replacementStrings[$sectionToInclude->getSlug()]] = $sectionToInclude;
                    }
                }
            }

            // C: replace keys by sections content
            foreach ($items as $item) {
                if ($item instanceof Item\Item) {
                    $content = $item->getContent();

                    // Insert extracted contents
                    foreach ($replacementValues as $key => $replacementSection) {
                        $replacementTemplate = $twig->render('frontend/'.$replacementSection->type.'/_embed.html.twig', array(
                            'section' => $replacementSection,
                        ));
                        $content = str_replace($key, $replacementTemplate, $content);
                    }

                    // Remove no replaced keys
                    foreach ($galleries as $key => $slug) {
                        $content = str_replace($key, '', $content);
                    }

                    $item->setContent($content);
                }
            }
        }
    }

    /**
     * Fill items attribute with section's persisted items.
     *
     * @param Section\SectionInterface $section
     *
     * @return boolean true if hydration is successful.
     */
    public function hydrateItems(Section\SectionInterface $section)
    {
        if ($section->getId() == null) {
            return false;
        }

        $sql = ItemProvider::baseQuery() .
            'WHERE i.expose_section_id = ? '.
            'ORDER BY i.hierarchy ASC ';

        $itemTranslations = $this->db->fetchAll($sql, array($section->getId()));

        if (empty($itemTranslations)) {
            return false;
        }

        foreach ($itemTranslations as $data) {
            $section->getDefaultItems()[$data['id']] = static::instantiateItem($data);
        }

        return true;
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
     * Persist a new section.
     *
     * @param \Ideys\Content\Section\Section $section
     *
     * @return \Ideys\Content\Section\Section
     */
    public function addSection(Section\Section &$section)
    {
        $count = $this->db->fetchAssoc('SELECT COUNT(s.id) AS total FROM expose_section AS s');
        $i = $count['total']++;

        $this->db->insert('expose_section', array(
            'expose_section_id' => $section->getExposeSectionId(),
            'type' => $section->getType(),
            'slug' => $this->uniqueSlug($section),
            'custom_css' => $section->getCustomCss(),
            'custom_js' => $section->getCustomJs(),
            'menu_pos' => $section->getMenuPos(),
            'target_blank' => $section->getTargetBlank(),
            'visibility' => $section->getVisibility(),
            'shuffle' => $section->getShuffle(),
            'archive' => 0,
            'hierarchy' => $i,
        ) + $this->blameAndTimestampData(0));

        $section->setId($this->db->lastInsertId());
        $this->db->insert('expose_section_trans', array(
            'expose_section_id' => $section->getId(),
            'title' => $section->getTitle(),
            'description' => $section->getDescription(),
            'legend' => $section->getLegend(),
            'language' => $this->language,
            'parameters' => serialize($section->getParameters()),
        ));

        return $section;
    }

    /**
     * Edit a section.
     *
     * @param \Ideys\Content\Section\Section $section
     */
    public function updateSection(Section\Section $section)
    {
        // Reset old homepage visibility in case of section
        // was newly defined as the homepage.
        // Also remove section from sub-folder.
        if ($section->isHomepage()) {
            $this->db->update('expose_section',
                array('visibility' => Section\Section::VISIBILITY_CLOSED),
                array('visibility' => Section\Section::VISIBILITY_HOMEPAGE)
            );
            $section->setExposeSectionId(null);
        }

        // Update section
        $this->db->update('expose_section', array(
            'slug' => $this->uniqueSlug($section),
            'custom_css' => $section->getCustomCss(),
            'custom_js' => $section->getCustomJs(),
            'tag' => $section->getTag(),
            'menu_pos' => $section->getMenuPos(),
            'target_blank' => $section->getTargetBlank(),
            'visibility' => $section->getVisibility(),
            'shuffle' => $section->getShuffle(),
            'expose_section_id' => $section->getExposeSectionId(),
        ) + $this->blameAndTimestampData($section->getId()),
        array('id' => $section->getId()));

        // Update translated section attributes
        $this->db->update('expose_section_trans', array(
            'title' => $section->getTitle(),
            'description' => $section->getDescription(),
            'legend' => $section->getLegend(),
            'parameters' => serialize($section->getParameters()),
        ), array('expose_section_id' => $section->getId(), 'language' => $this->language));

        // Update other sections parameters with identical tag
        if ($section->getTag() != null) {
            $this->updateGroupedSections($section);
        }
    }

    /**
     * Update all sections common parameters with identical tag.
     *
     * @param \Ideys\Content\Section\Section $section
     */
    private function updateGroupedSections(Section\Section $section)
    {
        $this->db->update('expose_section', array(
            'custom_css' => $section->getCustomCss(),
            'custom_js' => $section->getCustomJs(),
            'shuffle' => $section->getShuffle(),
        ),
        array('tag' => $section->getTag(), 'type' => $section->getType()));

        // Update translated sections parameters
        $sectionsIds = $this->db->fetchAll(
            'SELECT id FROM expose_section WHERE tag = ? AND type = ?',
            array($section->getTag(), $section->getType())
        );

        foreach ($sectionsIds as $id) {
            $this->db->update('expose_section_trans', array(
                'parameters' => serialize($section->getParameters()),
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
    protected function uniqueSlug(Section\Section $section)
    {
        $title = $section->getTitle();

        // Add a "-dir" suffix to dir sections.
        if ($section->getType() === Section\Section::SECTION_DIR) {
            $title .= '-dir';
        }

        $slug = String::slugify($title);

        $sections = $this->db->fetchAll(
            'SELECT slug FROM expose_section WHERE slug LIKE ? AND id != ?',
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

        return $slug;
    }

    /**
     * Insert a new content.
     *
     * @param \Ideys\Content\Section\Section    $section
     * @param \Ideys\Content\Item\Item          $item
     *
     * @return \Ideys\Content\Item\Item $item
     */
    public function addItem(Section\Section $section, Item\Item $item)
    {
        $this->db->insert('expose_section_item', array(
            'expose_section_id' => $section->getId(),
            'type' => $item->getType(),
            'category' => $item->getCategory(),
            'tags' => $item->getTags(),
            'slug' => String::slugify($item->getTitle()),
            'path' => $item->getPath(),
            'latitude' => $item->getLatitude(),
            'longitude' => $item->getLongitude(),
            'posting_date' => static::dateToDatabase($item->getPostingDate()),
            'author' => $item->getAuthor(),
            'published' => $item->getPublished(),
            'hierarchy' => $item->getHierarchy(),
        ) + $this->blameAndTimestampData(0));

        $item->setId($this->db->lastInsertId());
        $this->db->insert('expose_section_item_trans', array(
            'expose_section_item_id' => $item->getId(),
            'title' => $item->getTitle(),
            'description' => $item->getDescription(),
            'content' => $item->getContent(),
            'parameters' => serialize($item->getParameters()),
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
                'path' => $item->getPath(),
                'latitude' => $item->getLatitude(),
                'longitude' => $item->getLongitude(),
                'posting_date' => static::dateToDatabase($item->getPostingDate()),
                'tags' => $item->getTags(),
                'author' => $item->getAuthor(),
            ) + $this->blameAndTimestampData($item->getId()),
            array('id' => $item->getId())
        );
        $this->db->update(
            'expose_section_item_trans',
            array(
                'title' => $item->getTitle(),
                'description' => $item->getDescription(),
                'parameters' => serialize($item->getParameters()),
                'content' => $item->getContent(),
            ),
            array(
                'expose_section_item_id' => $item->getId(),
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
     * @param string  $tags
     * @param string  $link
     */
    public function updateItemTitle($id, $title, $description, $tags, $link)
    {
        $this->db->update(
            'expose_section_item',
            array(
                'tags' => $tags,
            ),
            array(
                'id' => $id,
            )
        );
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
