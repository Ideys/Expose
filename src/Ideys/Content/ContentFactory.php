<?php

namespace Ideys\Content;

use Ideys\Content\Section;
use Ideys\Content\Item;
use Ideys\String;
use Ideys\Settings\Settings;
use Silex\Application;
use Symfony\Component\Security\Core\User\User;
use Doctrine\DBAL\Connection;
use Imagine;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\FormFactory;

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

        $sql = $this::getSqlSelectSection()
           . 'WHERE t.language = ? '
           . 'GROUP BY s.id '
           . 'ORDER BY s.hierarchy ASC ';
        $sections = $this->db->fetchAll($sql, array($this->language));

        // Use sql primary keys as array keys and objectify entity
        foreach ($sections as $section) {
            $this->sections[$section['id']] = static::instantiateSection($section);
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
     * Return the first viewable section.
     *
     * @return \Ideys\Content\Section\Section
     */
    public function findFirstSection()
    {
        $sql = $this::getSqlSelectSection()
            . "WHERE s.type NOT IN ('link', 'dir')"
            . "AND s.visibility NOT IN ('homepage', 'closed') ";
        $sectionTranslations = $this->db->fetchAll($sql);

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
        $sectionTranslations = $this->db->fetchAll($sql, array(Section\Section::VISIBILITY_HOMEPAGE));
        $section = $this->hydrateSection($sectionTranslations);

        // Generate default homepage
        if (null === $section->getId()) {
            $settings = new Settings($this->db);
            $section = $this->addSection(new Section\Html($this->db, array(
                'type' => Section\Section::SECTION_HTML,
                'title' => $settings->name,
                'visibility' => Section\Section::VISIBILITY_HOMEPAGE,
            )));
            $page = new Item\Page(array(
                'type' => Item\Item::ITEM_PAGE,
                'title' => $settings->name,
                'content' => '<div id="homepage"><h1>'.$settings->name.'</h1></div>',
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
     * @param \Ideys\Content\Section\Section $section
     * @param $twig
     */
    public function composeSectionItems($section, \Twig_Environment $twig)
    {
        if ($section->isComposite()) {

            $items = $section->getItems($section::getDefaultItemType());

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
                $sql = $this::getSqlSelectSection()
                    . 'WHERE s.slug IN (\''. implode("', '", $sanitizedSlugs) .'\') '
                    . 'AND t.language = ? '
                    . "AND s.type IN ('gallery', 'channel') ";

                $sectionsToInclude = $this->db->fetchAll($sql, array($this->language));

                $replacementStrings = array_flip($galleries);
                foreach ($sectionsToInclude as $s) {
                    $sectionToInclude = static::instantiateSection($s);
                    $sectionToInclude->hydrateItems();
                    $defaultType = $sectionToInclude::getDefaultItemType();
                    if ($sectionToInclude->hasItems($defaultType)) {
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
     * Delete a section item.
     *
     * @param integer $id
     *
     * @return boolean
     */
    public function deleteItem($id)
    {
        // Delete item's translations
        $this->db->delete('expose_section_item_trans', array('expose_section_item_id' => $id));
        // Delete item
        $rows = $this->db->delete('expose_section_item', array('id' => $id));

        return (0 < $rows);
    }

    /**
     * Delete a selection of slides.
     *
     * @param array    $itemIds
     *
     * @return array
     */
    public function deleteSlides($itemIds)
    {
        $deletedIds = array();

        foreach ($itemIds as $id) {
            if (is_numeric($id)
                && $this->deleteItemAndRelatedFile($this->items[$id])) {
                $deletedIds[] = $id;
            }
        }
        return $deletedIds;
    }

    /**
     * Return section settings form builder used to extends standard form.
     *
     * @param \Symfony\Component\Form\FormFactory $formFactory
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function settingsFormBuilder(FormFactory $formFactory)
    {
        $sectionType = new SectionType($this->db, $formFactory);

        $formBuilder = $sectionType->formBuilder($this);
        $formBuilder->remove('type');

        return $formBuilder;
    }

    /**
     * Return section settings form.
     *
     * @param \Symfony\Component\Form\FormFactory $formFactory
     *
     * @return \Symfony\Component\Form\Form
     */
    public function settingsForm(FormFactory $formFactory)
    {
        return $this->settingsFormBuilder($formFactory)->getForm();
    }

    /**
     * Fill items attribute with section's persisted items.
     *
     * @param Section\Section $section
     *
     * @return boolean true if hydration is successful.
     */
    public function hydrateItems(Section\Section $section)
    {
        if ($section->getId() == null) {
            return false;
        }

        $sql = static::getSqlSelectItem() .
            'WHERE i.expose_section_id = ? '.
            'ORDER BY i.hierarchy ASC ';

        $itemTranslations = $this->db->fetchAll($sql, array($section->getId()));

        if (empty($itemTranslations)) {
            return false;
        }

        foreach ($itemTranslations as $data) {
            $section->getItems($section::getDefaultItemType())[$data['id']] = static::instantiateItem($data);
        }

        return true;
    }

    /**
     * Add a slide into gallery.
     *
     * @param \Imagine\Image\ImagineInterface                       $imagine
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile   $file
     *
     * @return \Ideys\Content\Item\Slide
     */
    public function addSlide(Imagine\Image\ImagineInterface $imagine, UploadedFile $file)
    {
        $fileExt = $file->guessClientExtension();
        $realExt = $file->guessExtension();// from mime type
        $fileSize = $file->getClientSize();

        $slide = new Item\Slide(array(
            'category' => $file->getMimeType(),
            'type' => Item\Item::ITEM_SLIDE,
            'hierarchy' => ($this->countItems('Slide') + 1),
        ));

        $slide->setPath(uniqid('expose').'.'.$fileExt);
        $slide->addParameter('real_ext', $realExt);
        $slide->addParameter('file_size', $fileSize);
        $slide->addParameter('original_name', $file->getClientOriginalName());

        $file->move(static::getGalleryDir(), $slide->getPath());

        foreach ($this->thumbSizes as $thumbSize){
            $this->createResizeSlide($imagine, $slide, $thumbSize);
        }

        return $slide;
    }

    /**
     * Resize and save a slide file into dedicated directory.
     *
     * @param \Imagine\Image\ImagineInterface   $imagine
     * @param \Ideys\Content\Item\Slide         $slide
     * @param integer                           $maxWidth
     * @param integer                           $maxHeight
     *
     * @return \Ideys\Content\Item\Slide
     */
    public function createResizeSlide(Imagine\Image\ImagineInterface $imagine, Item\Slide $slide, $maxWidth, $maxHeight = null)
    {
        $maxHeight = (null == $maxHeight) ? $maxWidth : $maxHeight;

        $thumbDir = static::getGalleryDir().'/'.$maxWidth;
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir);
        }

        $transformation = new Imagine\Filter\Transformation();
        $transformation->thumbnail(new Imagine\Image\Box($maxWidth, $maxHeight))
            ->save($thumbDir.'/'.$slide->getPath());
        $transformation->apply($imagine
            ->open(static::getGalleryDir().'/'.$slide->getPath()));

        return $slide;
    }


    /**
     * Delete item's data entry and related files.
     *
     * @param \Ideys\Content\Item\Slide $slide
     *
     * @return boolean
     */
    protected function deleteItemAndRelatedFile(Item\Slide $slide)
    {
        if ($this->deleteItem($slide->getId())) {
            @unlink(WEB_DIR.'/gallery/'.$slide->getPath());
            foreach ($this->thumbSizes as $thumbSize){
                @unlink(WEB_DIR.'/gallery/'.$thumbSize.'/'.$slide->getPath());
            }
            return true;
        }
        return false;
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
     * Delete section and this items in database.
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
     * Return an Item.
     *
     * @param integer $id
     *
     * @return \Ideys\Content\Item\Item
     */
    public function findItem($id)
    {
        $sql = static::getSqlSelectItem()
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
     * Return instantiated Section from array data.
     *
     * @param array                     $data
     *
     * @return Section\Section
     *
     * @throws \Exception If Section type is unknown
     */
    public static function instantiateSection($data)
    {
        switch ($data['type']) {
            case Section\Section::SECTION_GALLERY:
                $section = new Section\Gallery();
                break;
            case Section\Section::SECTION_HTML:
                $section = new Section\Html();
                break;
            case Section\Section::SECTION_BLOG:
                $section = new Section\Blog();
                break;
            case Section\Section::SECTION_FORM:
                $section = new Section\Form();
                break;
            case Section\Section::SECTION_CHANNEL:
                $section = new Section\Channel();
                break;
            case Section\Section::SECTION_MAPS:
                $section = new Section\Maps();
                break;
            case Section\Section::SECTION_LINK:
                $section = new Section\Link();
                break;
            case Section\Section::SECTION_DIR:
                $section = new Section\Dir();
                break;
            default:
                throw new \Exception(sprintf('Unable to find a Section of type "%s".', $data['type']));
        }

        static::hydrator($section, $data);

        return $section;
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
        if (!in_array($data['type'], Item\Item::getTypes())) {
            $data['type'] = static::getDefaultSectionItemType($data['section_type']);
        }

        $itemClassName = '\Ideys\Content\Item\\'.ucfirst($data['type']);
        $itemClass = new $itemClassName();

        static::hydrator($itemClass, $data);

        return $itemClass;
    }

    /**
     * @param object $object
     * @param array  $data
     */
    private static function hydrator(&$object, $data)
    {
        $class = new \ReflectionClass($object);

        do {
            foreach ($class->getProperties() as $property) {
                $propertyName = $property->getName();
                if ($class->hasMethod('get' . ucfirst($propertyName))
                    && array_key_exists($propertyName, $data)) {

                    $object->{'set' . ucfirst($propertyName)}($data[$propertyName]);
                }
            }
        } while ($class = $class->getParentClass());
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
        $sectionTypes = Section\Section::getTypes();
        $sectionTypes = array_diff($sectionTypes, array(Section\Section::SECTION_LINK, Section\Section::SECTION_DIR));
        $itemTypes = Item\Item::getTypes();
        $sectionItems = array_combine($sectionTypes, $itemTypes);

        return $sectionItems[$type];
    }

    /**
     * Instantiate a related content object from database entity.
     *
     * @param array $sectionTranslations
     *
     * @return Section\Section
     */
    private function hydrateSection(array $sectionTranslations)
    {
        if (empty($sectionTranslations)) {
            return false;
        }

        $sectionData = $this->retrieveLanguage($sectionTranslations, $this->language);
        $section = static::instantiateSection($sectionData);
        $this->hydrateItems($section);
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
        'SELECT s.id, s.expose_section_id, s.connected_sections, '.
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
               't.link, t.parameters, t.language, '.
               'st.title AS section_title, s.type AS section_type '.
        'FROM expose_section_item AS i '.
        'LEFT JOIN expose_section_item_trans AS t '.
        'ON t.expose_section_item_id = i.id '.
        'LEFT JOIN expose_section AS s '.
        'ON i.expose_section_id = s.id '.
        'LEFT JOIN expose_section_trans AS st '.
        'ON st.expose_section_id = s.id ';
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
