<?php

namespace Ideys\Content\Provider;

use Ideys\Content\Item;

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
     * @return \Ideys\Content\Item\Item
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
     * @return \Ideys\Content\Item\Item
     */
    public static function hydrateItem(array $data)
    {
        $itemClassName = '\Ideys\Content\Item\\'.ucfirst($data['type']);
        $itemClass = new $itemClassName();

        static::hydrate($itemClass, $data);

        return $itemClass;
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
