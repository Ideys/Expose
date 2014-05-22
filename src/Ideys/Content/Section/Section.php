<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentFactory;
use Ideys\Content\ContentTrait;
use Ideys\Content\SectionType;
use Ideys\Content\Item\Slide;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\DBAL\Connection;
use Imagine;

/**
 * Sections prototype class.
 */
abstract class Section
{
    use ContentTrait;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * Define if shuffle mode is activated.
     *
     * @var boolean
     */
    protected $shuffleOn = false;

    /**
     * Section main attributes
     *
     * @var array
     */
    protected $attributes = array(
        'id' => null,
        'expose_section_id' => null,
        'type' => null,
        'title' => null,
        'description' => null,
        'legend' => null,
        'total_items' => 0,
        'custom_css' => null,
        'custom_js' => null,
        'tag' => null,
        'parameters' => 'N;',
        'menu_pos' => 'main',
        'target_blank' => '0',
        'visibility' => 'public',
        'shuffle' => '0',
        'language' => null,
        'archive' => '0',
    );

    /**
     * @var array
     */
    protected $items = array();

    /**
     * Slides thumbs sizes.
     *
     * @var array
     */
    protected $thumbSizes = array(1200, 220);

    /**
     * @var array
     */
    protected $sections = array();

    /**
     * @var string
     */
    protected $language = 'en';

    /**
     * Visibility states.
     */
    const VISIBILITY_HOMEPAGE   = 'homepage';
    const VISIBILITY_PUBLIC     = 'public';
    const VISIBILITY_PRIVATE    = 'private';
    const VISIBILITY_HIDDEN     = 'hidden';
    const VISIBILITY_CLOSED     = 'closed';

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     * @param array                     $entity
     */
    public function __construct(Connection $db, array $entity = array())
    {
        $this->db = $db;
        $this->attributes = array_merge($this->attributes, $entity);
        $this->parameters = (array) unserialize($this->attributes['parameters']);
    }

    /**
     * Add a child section to section.
     *
     * @param Section $section
     *
     * @return Section
     */
    public function addSection(Section $section)
    {
        $this->sections[] = $section;

        return $this;
    }

    /**
     * Return section child sections.
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Return section items.
     * Trigger the shuffle mode if set.
     *
     * @param string $type Items type.
     *
     * @return array
     */
    public function getItems($type)
    {
        $typeNamespace = '\Ideys\Content\Item\\'.$type;
        return array_filter($this->items, function($item) use ($typeNamespace) {
            return ($item instanceof $typeNamespace);
        });
    }

    /**
     * Trigger shuffle on section items if option was set.
     */
    public function triggerShuffle()
    {
        if ($this->shuffle && !$this->shuffleOn) {
            shuffle($this->items);
            $this->shuffleOn = true;
        }
    }

    /**
     * Return section item found by slug.
     *
     * @param string $slug
     *
     * @return \Ideys\Content\Item\Item|false
     */
    public function getItemFromSlug($slug)
    {
        foreach ($this->items as $item) {
            if ($slug == $item->slug) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Test if the section has more than one page.
     * Method extended by section children.
     *
     * @return boolean
     */
    public function hasMultiplePages()
    {
        return false;
    }

    /**
     * Define content translation language.
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Test if content is hidden from anonymous users.
     *
     * @return boolean
     */
    public function isPrivate()
    {
        return self::VISIBILITY_PRIVATE === $this->visibility;
    }

    /**
     * Test if content is not accessible.
     *
     * @return boolean
     */
    public function isClosed()
    {
        return self::VISIBILITY_CLOSED === $this->visibility;
    }

    /**
     * Test if the section is the homepage.
     *
     * @return boolean
     */
    public function isHomepage()
    {
        return self::VISIBILITY_HOMEPAGE === $this->visibility;
    }

    /**
     * Test if the section is archived.
     *
     * @return boolean
     */
    public function isArchived()
    {
        return 1 == $this->archive;
    }

    /**
     * Test if the section have to be displayed into menu.
     *
     * @param boolean $userHasCredentials
     *
     * @return boolean
     */
    public function isMenuEnabled($userHasCredentials = false)
    {
        return !$this->isArchived()
            && !in_array($this->visibility, array(
                self::VISIBILITY_HOMEPAGE,
                self::VISIBILITY_HIDDEN,
                self::VISIBILITY_CLOSED
            ))
            && (
                ($this->visibility !== self::VISIBILITY_PRIVATE)
              || $userHasCredentials);
    }

    /**
     * Test if the section could have slides items.
     *
     * @return boolean
     */
    public function isSlidesHolder()
    {
        return false;
    }

    /**
     * Test if content has some items or not.
     *
     * @param string $type Items type.
     *
     * @return boolean
     */
    public function hasItems($type)
    {
        return count($this->getItems($type)) > 0;
    }

    /**
     * Return the gallery directory path for slide parent sections.
     *
     * @return string
     */
    public static function getGalleryDir()
    {
        return WEB_DIR.'/gallery';
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
     * @return boolean true if hydration is successful.
     */
    public function hydrateItems()
    {
        $sql = ContentFactory::getSqlSelectItem() .
              'WHERE i.expose_section_id = ?'.
              'ORDER BY i.hierarchy ASC ';

        $itemTranslations = $this->db->fetchAll($sql, array($this->id));

        if (empty($itemTranslations)) {
            return false;
        }

        foreach ($itemTranslations as $data) {
            $this->items[$data['id']] = ContentFactory::instantiateItem($data);
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

        $this->total_items += 1;
        $slide = new Slide(array(
            'category' => $file->getMimeType(),
            'type' => ContentFactory::ITEM_SLIDE,
            'hierarchy' => $this->total_items,
        ));

        $slide->path = uniqid('expose').'.'.$fileExt;
        $slide->setParameter('real_ext', $realExt);
        $slide->setParameter('file_size', $fileSize);
        $slide->setParameter('original_name', $file->getClientOriginalName());

        $file->move(static::getGalleryDir(), $slide->path);

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
    public function createResizeSlide(Imagine\Image\ImagineInterface $imagine, Slide $slide, $maxWidth, $maxHeight = null)
    {
        $maxHeight = (null == $maxHeight) ? $maxWidth : $maxHeight;

        $thumbDir = static::getGalleryDir().'/'.$maxWidth;
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir);
        }

        $transformation = new Imagine\Filter\Transformation();
        $transformation->thumbnail(new Imagine\Image\Box($maxWidth, $maxHeight))
            ->save($thumbDir.'/'.$slide->path);
        $transformation->apply($imagine
            ->open(static::getGalleryDir().'/'.$slide->path));

        return $slide;
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
     * Delete item's data entry and related files.
     *
     * @param \Ideys\Content\Item\Slide $slide
     *
     * @return boolean
     */
    protected function deleteItemAndRelatedFile(Slide $slide)
    {
        if ($this->deleteItem($slide->id)) {
            @unlink(WEB_DIR.'/gallery/'.$slide->path);
            foreach ($this->thumbSizes as $thumbSize){
                @unlink(WEB_DIR.'/gallery/'.$thumbSize.'/'.$slide->path);
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
            if ($item instanceof Slide) {
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
}
