<?php

namespace App\Content\Section;

use App\Content\AbstractEntity;
use App\Content\Item\Item;

class Section extends AbstractEntity
{
    /**
     * @var integer
     */
    protected $parentId;

    /**
     * @var string
     */
    protected $type = 'section';

    const SECTION_GALLERY   = 'gallery';
    const SECTION_CHANNEL   = 'channel';
    const SECTION_HTML      = 'html';
    const SECTION_BLOG      = 'blog';
    const SECTION_FORM      = 'form';
    const SECTION_MAP       = 'map';
    const SECTION_LINK      = 'link';
    const SECTION_DIR       = 'dir';

    /**
     * @var string
     */
    protected $legend;

    /**
     * @var string
     */
    protected $customCss;

    /**
     * @var string
     */
    protected $customJs;

    /**
     * @var string
     */
    protected $tag;

    /**
     * @var string
     */
    protected $menuPos = self::MENU_POS_MAIN;

    const MENU_POS_MAIN     = 'main';
    const MENU_POS_SECOND   = 'second';

    /**
     * @var string
     */
    protected $targetBlank = '0';

    /**
     * @var string
     */
    protected $visibility = self::VISIBILITY_PUBLIC;

    const VISIBILITY_HOMEPAGE   = 'homepage';
    const VISIBILITY_PUBLIC     = 'public';
    const VISIBILITY_PRIVATE    = 'private';
    const VISIBILITY_HIDDEN     = 'hidden';
    const VISIBILITY_CLOSED     = 'closed';

    /**
     * @var string
     */
    protected $shuffle = '0';

    /**
     * @var string
     */
    protected $archive = '0';

    /**
     * Define if shuffle mode is activated.
     *
     * @var boolean
     */
    protected $shuffleOn = false;

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @var array
     */
    protected $connectedSectionsId = array();

    /**
     * Hold connected sections objects
     * retrieved from $connectedSectionsId.
     *
     * @var array
     */
    protected $connectedSections = array();

    /**
     * @var Section[]
     */
    protected $sections = array();

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
     */
    public function getItemFromSlug(string $slug): ?Item
    {
        foreach ($this->items as $item) {
            if ($slug == $item->slug) {
                return $item;
            }
        }

        return null;
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
     * Test if Section has a twin Section connected.
     *
     * @return boolean
     */
    public function isPaired()
    {
        return ((int) $this->parentId) > 0;
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
     * Test if the section could be composed with other sections items.
     *
     * @return boolean
     */
    public function isComposite()
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
    public function hasItemsOfType($type)
    {
        return $this->countItemsOfType($type) > 0;
    }

    /**
     * Return the number of items of a type into section.
     *
     * @param string $type Items type.
     *
     * @return integer
     */
    public function countItemsOfType($type)
    {
        return count($this->getItemsOfType($type));
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     *
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Return section types keys.
     *
     * @return array
     */
    public static function getTypes()
    {
        return array(
            self::SECTION_GALLERY,
            self::SECTION_CHANNEL,
            self::SECTION_HTML,
            self::SECTION_BLOG,
            self::SECTION_FORM,
            self::SECTION_MAP,
            self::SECTION_LINK,
            self::SECTION_DIR,
        );
    }

    /**
     * Return section type choices.
     *
     * @return array
     */
    public static function getTypeChoices()
    {
        return array(
            self::SECTION_GALLERY   => 'section.gallery',
            self::SECTION_CHANNEL   => 'section.channel',
            self::SECTION_HTML      => 'section.html',
            self::SECTION_BLOG      => 'section.blog',
            self::SECTION_FORM      => 'section.form',
            self::SECTION_MAP       => 'section.map',
            self::SECTION_LINK      => 'section.link',
            self::SECTION_DIR       => 'section.dir',
        );
    }

    /**
     * @return string
     */
    public function getLegend()
    {
        return $this->legend;
    }

    /**
     * @param string $legend
     *
     * @return $this
     */
    public function setLegend($legend)
    {
        $this->legend = $legend;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomCss()
    {
        return $this->customCss;
    }

    /**
     * @param string $customCss
     *
     * @return $this
     */
    public function setCustomCss($customCss)
    {
        $this->customCss = $customCss;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomJs()
    {
        return $this->customJs;
    }

    /**
     * @param string $customJs
     *
     * @return $this
     */
    public function setCustomJs($customJs)
    {
        $this->customJs = $customJs;

        return $this;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     *
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return string
     */
    public function getMenuPos()
    {
        return $this->menuPos;
    }

    /**
     * @param string $menuPos
     *
     * @return $this
     */
    public function setMenuPos($menuPos)
    {
        $this->menuPos = $menuPos;

        return $this;
    }

    /**
     * Return menu position choices.
     *
     * @return array
     */
    public static function getMenuPosChoices()
    {
        return array(
            self::MENU_POS_MAIN     => 'section.menu.main',
            self::MENU_POS_SECOND   => 'section.menu.second',
        );
    }

    /**
     * @return string
     */
    public function getTargetBlank()
    {
        return $this->targetBlank;
    }

    /**
     * @param string $targetBlank
     *
     * @return $this
     */
    public function setTargetBlank($targetBlank)
    {
        $this->targetBlank = $targetBlank;

        return $this;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     *
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Return content visibility choices.
     *
     * @return array
     */
    public static function getVisibilityChoices()
    {
        return array(
            self::VISIBILITY_HOMEPAGE    => 'section.visibility.homepage',
            self::VISIBILITY_PUBLIC      => 'section.visibility.public',
            self::VISIBILITY_PRIVATE     => 'section.visibility.private',
            self::VISIBILITY_HIDDEN      => 'section.visibility.hidden',
            self::VISIBILITY_CLOSED      => 'section.visibility.closed',
        );
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
     * @return string
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * @param string $shuffle
     *
     * @return $this
     */
    public function setShuffle($shuffle)
    {
        $this->shuffle = $shuffle;

        return $this;
    }

    /**
     * @return string
     */
    public function getArchive()
    {
        return $this->archive;
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
     * @param string $archive
     *
     * @return $this
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * @return $this
     */
    public function toggleArchive()
    {
        $this->archive = ! $this->archive;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getShuffleOn()
    {
        return $this->shuffleOn;
    }

    /**
     * @param boolean $shuffleOn
     *
     * @return $this
     */
    public function setShuffleOn($shuffleOn)
    {
        $this->shuffleOn = $shuffleOn;

        return $this;
    }

    /**
     * Return section items.
     *
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Return section items filtered by type.
     *
     * @param string $type Items type.
     *
     * @return array
     */
    public function getItemsOfType($type)
    {
        $typeNamespace = '\Ideys\Content\Item\Entity\\'.$type;
        return array_filter($this->items, function($item) use ($typeNamespace) {
            return ($item instanceof $typeNamespace);
        });
    }

    /**
     * @param array $items
     *
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Return connected sections id.
     *
     * @return array
     */
    public function getConnectedSectionsId()
    {
        return $this->connectedSectionsId;
    }

    /**
     * @param array $connectedSectionsId
     *
     * @return $this
     */
    public function setConnectedSectionsId($connectedSectionsId)
    {
        $this->connectedSectionsId = $connectedSectionsId;

        return $this;
    }

    /**
     * Add a connected section id.
     *
     * @param integer $connectedSectionsId
     *
     * @return Section
     */
    public function addConnectedSectionId($connectedSectionsId)
    {
        $this->connectedSectionsId[] = $connectedSectionsId;

        return $this;
    }

    /**
     * Remove a connected section id.
     *
     * @param integer $sectionId
     *
     * @return Section
     */
    public function removeConnectedSectionId($sectionId)
    {
        $this->connectedSectionsId = array_filter($this->connectedSectionsId, function($var) use ($sectionId) {
            return $var != $sectionId;
        });

        return $this;
    }

    /**
     * Remove or add a connected section id.
     *
     * @param integer $sectionId
     *
     * @return Section
     */
    public function toggleConnectedSectionId($sectionId)
    {
        if (in_array($sectionId, $this->connectedSectionsId)) {
            $this->removeConnectedSectionId($sectionId);
        } else {
            $this->addConnectedSectionId($sectionId);
        }

        return $this;
    }

    /**
     * @return Section[]
     */
    public function getConnectedSections()
    {
        return $this->connectedSections;
    }

    /**
     * @param Section $connectedSection
     *
     * @return Section
     */
    public function addConnectedSection(Section $connectedSection)
    {
        $this->connectedSections[] = $connectedSection;

        return $this;
    }

    /**
     * Return section child sections.
     *
     * @return Section[]
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param array $sections
     *
     * @return $this
     */
    public function setSections($sections)
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * Add a child section to section.
     *
     * @param Section $section
     *
     * @return $this
     */
    public function addSection(Section $section)
    {
        $this->sections[] = $section;

        return $this;
    }

    /**
     * Return menu enabled child sections.
     *
     * @param boolean $userHasCredentials
     *
     * @return Section[]
     */
    public function getActiveSections($userHasCredentials = false)
    {
        $activeSections = array();

        foreach ($this->sections as $childSection) {
            if ($childSection->isMenuEnabled($userHasCredentials)) {
                $activeSections[] = $childSection;
            }
        }

        return $activeSections;
    }
}
