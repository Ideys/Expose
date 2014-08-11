<?php

namespace Ideys\Content\Provider;

use Ideys\Content\Item;

/**
 * Blog section provider.
 */
class BlogProvider extends SectionProvider
{
    /**
     * {@inheritdoc}
     */
    public static function baseQuery()
    {
        return static::baseQuery() .
        'WHERE i.expose_section_id = ? '.
        'ORDER BY i.posting_date DESC, i.hierarchy ASC ';
    }
}
