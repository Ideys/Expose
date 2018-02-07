<?php

namespace Ideys\Content\Item\Provider;

/**
 * Blog Post item provider.
 */
class PostProvider extends ItemProvider
{
    /**
     * @return string
     */
    public static function findAllQuery()
    {
        return parent::baseQuery() .
        'WHERE i.expose_section_id = ? '.
        'ORDER BY i.posting_date DESC, i.hierarchy ASC ';
    }
}
