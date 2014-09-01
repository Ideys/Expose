<?php

namespace Ideys\Content\Item\Provider;

use Ideys\Content\AbstractProvider;
use Ideys\Content\Item\Entity\Item;
use Ideys\Content\Section\Entity\Section;
use Ideys\String;

/**
 * Item provider global class.
 */
class ItemProvider extends AbstractProvider
{
    /**
     * Return an Item.
     *
     * @param integer $id
     *
     * @return Item
     */
    public function find($id)
    {
        $sql = static::baseQuery()
            . 'WHERE i.id = ? '
            . 'ORDER BY s.hierarchy ASC ';
        $data = $this->db->fetchAssoc($sql, array($id));

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
     * @param Section    $section
     * @param Item       $item
     *
     * @return Item $item
     */
    public function create(Section $section, Item $item)
    {
        $item->setLanguage($this->language);
        $item->setSlug(String::slugify($item->getTitle()));
        $item->setExposeSectionId($section->getId());

        $this->blameAndTimestamp($item);

        $this->db->insert('expose_section_item', array());

        $item->setId($this->db->lastInsertId());
        $this->db->insert('expose_section_item_trans', array(
            'expose_section_item_id' => $item->getId(),
        ));

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
        $this->blameAndTimestamp($item);

        $this->db->update(
            'expose_section_item',
            array(),
            array('id' => $item->getId())
        );
        $this->db->update(
            'expose_section_item_trans',
            array(),
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
     * @param integer $id
     *
     * @return boolean
     */
    public function delete($id)
    {
        // Delete item's translations
        $this->db->delete('expose_section_item_trans', array('expose_section_item_id' => $id));
        // Delete item
        $rows = $this->db->delete('expose_section_item', array('id' => $id));

        return (0 < $rows);
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
            'FROM expose_section_item AS i '.
            'LEFT JOIN expose_section_item_trans AS t '.
            'ON t.expose_section_item_id = i.id '.
            'LEFT JOIN expose_section AS s '.
            'ON i.expose_section_id = s.id '.
            'LEFT JOIN expose_section_trans AS st '.
            'ON st.expose_section_id = s.id ';
    }
}
