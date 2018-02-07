<?php

namespace Ideys\Content\Item\Provider;

use Ideys\Content\AbstractProvider;
use Ideys\Content\Item\Entity\Item;
use Ideys\Content\Item\Entity\Slide;
use Ideys\Content\Section\Entity\Section;
use Ideys\StringHelper;

/**
 * Item provider global class.
 */
class ItemProvider extends AbstractProvider
{
    /**
     * Slides thumbs sizes.
     *
     * @var array
     */
    protected $thumbSizes = array(1200, 220);

    /**
     * Return an Item.
     *
     * @param integer $id
     *
     * @return Item|null
     */
    public function find($id)
    {
        $sql = static::baseQuery()
            . 'WHERE i.id = ? '
            . 'ORDER BY s.hierarchy ASC ';
        $data = $this->db->fetchAssoc($sql, array($id));

        if (! $data) {
            return null;
        }

        return static::hydrateItem($data);
    }

    /**
     * Return instantiated Item from array data.
     *
     * @param array $data
     *
     * @return Item
     */
    public static function hydrateItem(array $data)
    {
        $itemClassName = '\Ideys\Content\Item\Entity\\'.ucfirst($data['type']);
        $itemClass = new $itemClassName();

        static::hydrate($itemClass, $data);

        return $itemClass;
    }

    /**
     * Insert a new item.
     *
     * @param Section $section
     * @param Item    $item
     *
     * @return Item $item
     */
    public function create(Section $section, Item $item)
    {
        $item->setLanguage($this->language);
        $item->setSlug(StringHelper::slugify($item->getTitle()));
        $item->setExposeSectionId($section->getId());

        $this->blameAndTimestamp($item);

        $itemData = $this->objectToArray('section_item', $item);

        $this->db->insert(TABLE_PREFIX.'section_item', $itemData);

        $item->setId($this->db->lastInsertId());

        $translationData = $this->objectToArray('section_item_trans', $item);
        $translationData['expose_section_item_id'] = $item->getId();

        $this->db->insert(TABLE_PREFIX.'section_item_trans', $translationData);

        return $item;
    }

    /**
     * Update an item.
     *
     * @param Item $item
     *
     * @return Item
     */
    public function update(Item $item)
    {
        $item->setSlug(StringHelper::slugify($item->getTitle()));
        $this->blameAndTimestamp($item);

        $itemData = $this->objectToArray('section_item', $item);

        $this->db->update(
            TABLE_PREFIX.'section_item',
            $itemData,
            array('id' => $item->getId())
        );

        $translationData = $this->objectToArray('section_item_trans', $item);
        $translationData['expose_section_item_id'] = $item->getId();

        $this->db->update(
            TABLE_PREFIX.'section_item_trans',
            $translationData,
            array(
                'expose_section_item_id' => $item->getId(),
                'language' => $this->language,
            )
        );

        return $item;
    }

    /**
     * Delete a section item.
     *
     * @param Item $item
     *
     * @return boolean
     */
    public function delete(Item $item)
    {
        // Delete item's translations
        $this->db->delete(TABLE_PREFIX.'section_item_trans', array(
            'expose_section_item_id' => $item->getId(),
        ));

        // Delete item
        $rows = $this->db->delete(TABLE_PREFIX.'section_item', array(
            'id' => $item->getId(),
        ));

        // Delete related slides if exists and item removed from database
        if ((0 < $rows) && ($item instanceof Slide)) {
            @unlink(WEB_DIR.'/gallery/'.$item->getPath());
            foreach ($this->thumbSizes as $thumbSize){
                @unlink(WEB_DIR.'/gallery/'.$thumbSize.'/'.$item->getPath());
            }
        }

        return (0 < $rows);
    }

    /**
     * Delete a selection of items.
     *
     * @param Section $section
     * @param array   $itemIds
     *
     * @return array
     */
    public function deleteSelection(Section $section, $itemIds)
    {
        $deletedIds = array();
        $items = $section->getItems();

        foreach ($items as $item) {
            if (in_array($item->getId(), $itemIds)
                && $this->delete($item)) {
                $deletedIds[] = $item->getId();
            }
        }

        return $deletedIds;
    }

    /**
     * Return SQL statement to extract an Item.
     *
     * @return string
     */
    public static function baseQuery()
    {
        return
            'SELECT i.*, t.title, t.description, t.content, '.
            't.link, t.parameters, t.language, '.
            'st.title AS section_title, s.type AS section_type '.
            'FROM '.TABLE_PREFIX.'section_item AS i '.
            'LEFT JOIN '.TABLE_PREFIX.'section_item_trans AS t '.
            'ON t.expose_section_item_id = i.id '.
            'LEFT JOIN '.TABLE_PREFIX.'section AS s '.
            'ON i.expose_section_id = s.id '.
            'LEFT JOIN '.TABLE_PREFIX.'section_trans AS st '.
            'ON st.expose_section_id = s.id ';
    }
}
